<?php

namespace Wexample\SymfonyRemotePdfFactory\Class;

/**
 * A file the PDF carries inside it, unseen on the page: the data a document was
 * printed from, the source it was built with. pdf-factory stores it as given.
 *
 * Embedding a file requires the document's `created` date: the engine has no clock.
 * The relationship is written only in a PDF/A-3 output (see PdfOutput).
 */
class PdfAttachment
{
    public const string RELATIONSHIP_DATA = 'Data';
    public const string RELATIONSHIP_SOURCE = 'Source';
    public const string RELATIONSHIP_ALTERNATIVE = 'Alternative';
    public const string RELATIONSHIP_SUPPLEMENT = 'Supplement';

    /**
     * @param string $name    A plain file name, unique within one render (`invoice.json`).
     * @param string $content The raw bytes; encoded when posted.
     */
    public function __construct(
        public readonly string $name,
        public readonly string $mediaType,
        public readonly string $content,
        public readonly string $relationship = self::RELATIONSHIP_DATA,
        public readonly ?string $description = null,
    ) {
    }

    public static function json(
        string $name,
        array $data,
        ?string $description = null
    ): self {
        return new self(
            $name,
            'application/json',
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            self::RELATIONSHIP_DATA,
            $description,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'mediaType' => $this->mediaType,
            'description' => $this->description,
            'relationship' => $this->relationship,
            'content' => base64_encode($this->content),
        ], fn ($value) => null !== $value);
    }
}
