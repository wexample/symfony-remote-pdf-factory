<?php

namespace Wexample\SymfonyRemotePdfFactory\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wexample\PhpRemote\Enum\RemoteState;
use Wexample\SymfonyRemotePdfFactory\Class\PdfAttachment;
use Wexample\SymfonyRemotePdfFactory\Class\PdfOutput;
use Wexample\SymfonyRemotePdfFactory\Class\PdfTheme;
use Wexample\SymfonyRemotePdfFactory\Exception\PdfFactoryException;
use Wexample\SymfonyRemotePdfFactory\Helper\DocumentNodeHelper;
use Wexample\SymfonyRemotePdfFactory\Service\PdfFactoryClient;

class PdfFactoryClientTest extends TestCase
{
    public function testTheDocumentAndItsThemeArePostedTogether(): void
    {
        $sent = null;
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$sent) {
            $sent = ['method' => $method, 'url' => $url, 'body' => json_decode($options['body'], true)];

            return new MockResponse('%PDF-1.7');
        });

        $document = DocumentNodeHelper::doc('Hello', [DocumentNodeHelper::section('body', [DocumentNodeHelper::paragraph('Hi')])]);
        $theme = (new PdfTheme('p { color: red; }'))->withVariables(['pagination-before' => 'Page ']);

        $pdf = (new PdfFactoryClient($http, 'http://pdf_factory:8000/'))->render($document, $theme);

        $this->assertSame('%PDF-1.7', $pdf);
        $this->assertSame('POST', $sent['method']);
        $this->assertSame('http://pdf_factory:8000/render', $sent['url']);
        $this->assertSame($document, $sent['body']['document']);
        $this->assertSame(['stylesheet' => 'p { color: red; }', 'variables' => ['pagination-before' => 'Page ']], $sent['body']['theme']);
    }

    public function testAttachmentsAndTheFacturXOutputArePostedBesideTheDocument(): void
    {
        $sent = null;
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$sent) {
            $sent = json_decode($options['body'], true);

            return new MockResponse('%PDF');
        });

        $document = DocumentNodeHelper::doc('Invoice F-001', [], info: [
            'author' => 'Example Workshop',
            'language' => 'fr-BE',
            'created' => '2026-10-12T09:30:00+02:00',
            'unknown' => 'dropped',
        ]);

        (new PdfFactoryClient($http, 'http://pdf'))->render(
            $document,
            attachments: [PdfAttachment::json('invoice.json', ['number' => 'F-001'], 'The figures')],
            output: PdfOutput::facturX('<rsm:CrossIndustryInvoice/>'),
        );

        $this->assertSame(['type' => 'doc', 'title' => 'Invoice F-001', 'author' => 'Example Workshop', 'language' => 'fr-BE', 'created' => '2026-10-12T09:30:00+02:00'], $sent['document']);
        $this->assertSame(['name', 'mediaType', 'description', 'relationship', 'content'], array_keys($sent['attachments'][0]));
        $this->assertSame(['number' => 'F-001'], json_decode(base64_decode($sent['attachments'][0]['content']), true));
        $this->assertSame('Data', $sent['attachments'][0]['relationship']);
        $this->assertSame(['variant' => 'pdf/a-3b', 'facturx' => ['conformance' => 'EN 16931', 'xml' => base64_encode('<rsm:CrossIndustryInvoice/>')]], $sent['output']);
    }

    public function testAnEmptyThemeIsNotSent(): void
    {
        $sent = null;
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$sent) {
            $sent = json_decode($options['body'], true);

            return new MockResponse('%PDF');
        });

        (new PdfFactoryClient($http, 'http://pdf'))->render(DocumentNodeHelper::doc('T', []), new PdfTheme());

        $this->assertArrayNotHasKey('theme', $sent);
        $this->assertArrayNotHasKey('attachments', $sent);
        $this->assertArrayNotHasKey('output', $sent);
    }

    public function testARefusalCarriesTheServiceAnswer(): void
    {
        $http = new MockHttpClient(new MockResponse('{"error":"$.content[0]: unknown node"}', ['http_code' => 422]));

        $this->expectException(PdfFactoryException::class);
        $this->expectExceptionMessage('unknown node');

        (new PdfFactoryClient($http, 'http://pdf'))->render(DocumentNodeHelper::doc('T', []));
    }

    public function testWithoutUrlTheRemoteIsUnconfigured(): void
    {
        $client = new PdfFactoryClient(new MockHttpClient(), null);

        $this->assertSame(RemoteState::Unconfigured, $client->checkStatus()->state);

        $this->expectException(PdfFactoryException::class);
        $client->render(DocumentNodeHelper::doc('T', []));
    }

    public function testCellsCarryOnlyTheAttributesTheyNeed(): void
    {
        $this->assertSame(['type' => 'tableCell', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'a']]]]], DocumentNodeHelper::textCell('a'));
        $this->assertSame(['align' => 'right', 'colspan' => 3], DocumentNodeHelper::textCell('a', 'right', 3)['attrs']);
        $this->assertSame([], DocumentNodeHelper::paragraphs(['', null]));
    }

    public function testThemeFilesAreJoinedInOrder(): void
    {
        $base = tempnam(sys_get_temp_dir(), 'css');
        $brand = tempnam(sys_get_temp_dir(), 'css');
        file_put_contents($base, 'a{}');
        file_put_contents($brand, 'b{}');

        $this->assertSame("a{}\n\nb{}", PdfTheme::fromFiles([$base, $brand])->stylesheet);
    }
}
