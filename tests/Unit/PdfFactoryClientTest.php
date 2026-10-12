<?php

namespace Wexample\SymfonyRemotePdfFactory\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wexample\PhpRemote\Enum\RemoteState;
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

    public function testAnEmptyThemeIsNotSent(): void
    {
        $sent = null;
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$sent) {
            $sent = json_decode($options['body'], true);

            return new MockResponse('%PDF');
        });

        (new PdfFactoryClient($http, 'http://pdf'))->render(DocumentNodeHelper::doc('T', []), new PdfTheme());

        $this->assertArrayNotHasKey('theme', $sent);
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
