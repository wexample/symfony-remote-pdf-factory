<?php

namespace Wexample\SymfonyRemotePdfFactory\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Wexample\PhpRemote\Class\RemoteStatus;
use Wexample\PhpRemote\Interface\RemoteInterface;
use Wexample\SymfonyRemotePdfFactory\Class\PdfTheme;
use Wexample\SymfonyRemotePdfFactory\Exception\PdfFactoryException;

/**
 * Posts a document and its charter to a pdf-factory service (POST /render) and gets
 * the PDF back. Its health (GET /health) is checked as a remote.
 */
class PdfFactoryClient implements RemoteInterface
{
    public function __construct(
        private readonly ?HttpClientInterface $httpClient = null,
        private readonly ?string $baseUrl = null,
    ) {
    }

    public function getKey(): string
    {
        return 'pdf_factory';
    }

    public function getLabel(): string
    {
        return 'PDF factory';
    }

    public function checkStatus(): RemoteStatus
    {
        if (! $this->isConfigured()) {
            return RemoteStatus::unconfigured('No pdf-factory URL.');
        }

        $response = $this->httpClient->request('GET', $this->url('/health'));

        return 200 === $response->getStatusCode() ? RemoteStatus::up() : RemoteStatus::down('HTTP '.$response->getStatusCode());
    }

    /**
     * @param array $document A `doc` node.
     * @return string The PDF bytes.
     */
    public function render(
        array $document,
        ?PdfTheme $theme = null
    ): string {
        if (! $this->isConfigured()) {
            throw new PdfFactoryException('pdf-factory is not configured (wexample_symfony_remote_pdf_factory.url).');
        }

        $payload = ['document' => $document];
        if ($theme && $themeData = $theme->toArray()) {
            $payload['theme'] = $themeData;
        }

        $response = $this->httpClient->request('POST', $this->url('/render'), ['json' => $payload]);

        if (200 !== $response->getStatusCode()) {
            throw new PdfFactoryException('pdf-factory refused the document: '.$response->getContent(false));
        }

        return $response->getContent();
    }

    public function isConfigured(): bool
    {
        return null !== $this->httpClient && null !== $this->baseUrl && '' !== $this->baseUrl;
    }

    private function url(string $path): string
    {
        return rtrim((string) $this->baseUrl, '/').$path;
    }
}
