# symfony-remote-pdf-factory

Version: 2.0.3

```php
use Wexample\SymfonyRemotePdfFactory\Class\PdfTheme;
use Wexample\SymfonyRemotePdfFactory\Helper\DocumentNodeHelper as Node;

$document = Node::doc('Gift card 0042', [
    Node::section('body', [
        Node::heading('Gift card', 1),
        Node::section('amount', [Node::paragraph('50,00 €', [Node::bold()])]),
        Node::qrCode('https://shop.example/redeem/0042', 'Redeem online'),
    ]),
], meta: ['reference' => [Node::paragraph('0042')]]);

$theme = PdfTheme::fromFiles([$baseCss, $brandCss], ['pagination-before' => 'Page '])
    ->withBlock('logo', [$imageNode]);

$pdf = $pdfFactoryClient->render($document, $theme);   // PDF bytes
```

A section is named by what it is (`issuer`, `totals`), never by how it looks: the stylesheet selects `section[data-role="totals"]`. `meta` entries run as `meta-<name>` for the margin boxes; `variables` give the words around the page counters. Fonts and images travel as data URIs, nothing is fetched. The vocabulary and its rules are pdf-factory's (`SERVICES/local/pdf-factory`, `specifications/document-model` and `specifications/theme`).

A refused document raises `PdfFactoryException` with the service's answer, which names the JSON path at fault.

## Describing the file, embedding files, Factur-X

```php
$document = Node::doc('Invoice F-001', $content, $meta, info: [
    'author' => 'Example Workshop',
    'language' => 'fr-BE',
    'created' => $emittedAt->format(DATE_ATOM),
]);

$pdf = $pdfFactoryClient->render(
    $document,
    $theme,
    attachments: [PdfAttachment::json('invoice.json', $data)],
    output: PdfOutput::facturX($ciiXml, PdfOutput::FACTURX_EN16931),
);
```

`info` fills the file's properties (`author`, `subject`, `keywords`, `language`, `created`). As soon as a file is embedded, `created` is required: the engine reads no clock, so two renders of one request are the same bytes. `PdfOutput::facturX()` asks for PDF/A-3b and hands over the CII XML; pdf-factory names it `factur-x.xml` and writes the XMP itself. Validating the XML against its profile is the caller's job. Reading attachments back is not the service's: use a PDF library (`specifications/embedded-files` in pdf-factory).

## Table of Contents

- [Describing the file, embedding files, Factur-X](#describing-the-file-embedding-files-factur-x)
- [Integration in the Suite](#integration-in-the-suite)
- [Dependencies](#dependencies)
- [Versioning & Compatibility Policy](#versioning--compatibility-policy)
- [License](#license)
- [About us](#about-us)
- [Migration Notes](#migration-notes)

## Integration in the Suite

This package is part of the Wexample Suite — a collection of high-quality, modular tools designed to work seamlessly together across multiple languages and environments.

### Related Packages

The suite includes packages for configuration management, file handling, prompts, and more. Each package can be used independently or as part of the integrated suite.

## Dependencies

- php: >=8.5
- wexample/symfony-helpers: >=18.0.0
- wexample/symfony-remote: >=2.0.0
- symfony/http-client: >=7.0

## Versioning & Compatibility Policy

Wexample packages follow **Semantic Versioning** (SemVer):

- **MAJOR**: Breaking changes
- **MINOR**: New features, backward compatible
- **PATCH**: Bug fixes, backward compatible

We maintain backward compatibility within major versions and provide clear migration guides for breaking changes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

Free to use in both personal and commercial projects.

## About us

[Wexample](https://wexample.com) stands as a cornerstone of the digital ecosystem — a collective of seasoned engineers, researchers, and creators driven by a relentless pursuit of technological excellence. More than a media platform, it has grown into a vibrant community where innovation meets craftsmanship, and where every line of code reflects a commitment to clarity, durability, and shared intelligence.

This packages suite embodies this spirit. Trusted by professionals and enthusiasts alike, it delivers a consistent, high-quality foundation for modern development — open, elegant, and battle-tested. Its reputation is built on years of collaboration, refinement, and rigorous attention to detail, making it a natural choice for those who demand both robustness and beauty in their tools.

Wexample cultivates a culture of mastery. Each package, each contribution carries the mark of a community that values precision, ethics, and innovation — a community proud to shape the future of digital craftsmanship.

Built and maintained by [Romain Weeger](https://weeger.io).

## Migration Notes

When upgrading between major versions, refer to the migration guides in the documentation.

Breaking changes are clearly documented with upgrade paths and examples.
