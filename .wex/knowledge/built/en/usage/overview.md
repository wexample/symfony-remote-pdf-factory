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
