<?php

namespace Wexample\SymfonyRemotePdfFactory\Class;

/**
 * What kind of file a render produces, beyond the plain PDF: neither meaning nor
 * appearance, so neither in the document nor in the theme.
 *
 * Factur-X: the caller gives the CII XML and its profile; pdf-factory names the file
 * `factur-x.xml`, embeds it and writes the XMP itself. Validating the XML against
 * its profile is the caller's job.
 */
class PdfOutput
{
    public const string VARIANT_PDF_A_3B = 'pdf/a-3b';

    public const string FACTURX_MINIMUM = 'MINIMUM';
    public const string FACTURX_BASIC_WL = 'BASIC WL';
    public const string FACTURX_BASIC = 'BASIC';
    public const string FACTURX_EN16931 = 'EN 16931';
    public const string FACTURX_EXTENDED = 'EXTENDED';

    public function __construct(
        public readonly ?string $variant = null,
        public readonly ?string $facturxXml = null,
        public readonly ?string $facturxConformance = null,
        public readonly ?string $facturxRelationship = null,
    ) {
    }

    public static function pdfA3b(): self
    {
        return new self(self::VARIANT_PDF_A_3B);
    }

    /**
     * @param string      $xml          The CII invoice, raw; encoded when posted.
     * @param string|null $relationship `Data` when null, the engine's default; MINIMUM and BASIC WL accept nothing else.
     */
    public static function facturX(
        string $xml,
        string $conformance = self::FACTURX_EN16931,
        ?string $relationship = null
    ): self {
        return new self(self::VARIANT_PDF_A_3B, $xml, $conformance, $relationship);
    }

    public function toArray(): array
    {
        return array_filter([
            'variant' => $this->variant,
            'facturx' => null === $this->facturxXml ? null : array_filter([
                'conformance' => $this->facturxConformance,
                'relationship' => $this->facturxRelationship,
                'xml' => base64_encode($this->facturxXml),
            ]),
        ]);
    }
}
