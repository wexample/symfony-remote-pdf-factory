<?php

namespace Wexample\SymfonyRemotePdfFactory\Helper;

/**
 * Nodes of pdf-factory's document model (ProseMirror / TipTap JSON), as arrays.
 * The model says what each part is; the look is the theme's.
 */
class DocumentNodeHelper
{
    /**
     * @param list<array>                $content
     * @param array<string, list<array>> $meta    Statements the document makes about itself, each running as `meta-<name>`.
     */
    public static function doc(
        string $title,
        array $content,
        array $meta = []
    ): array {
        return array_filter([
            'type' => 'doc',
            'title' => $title,
            'meta' => $meta,
            'content' => $content,
        ]);
    }

    /**
     * A part of the document named by what it is (`issuer`, `totals`), never by how it looks.
     *
     * @param list<array> $content
     */
    public static function section(
        string $role,
        array $content
    ): array {
        return ['type' => 'section', 'attrs' => ['role' => $role], 'content' => $content];
    }

    public static function heading(
        string $text,
        int $level
    ): array {
        return ['type' => 'heading', 'attrs' => ['level' => $level], 'content' => [self::text($text)]];
    }

    /**
     * @param list<array> $marks
     */
    public static function paragraph(
        string $text,
        array $marks = []
    ): array {
        return '' === $text
            ? ['type' => 'paragraph']
            : ['type' => 'paragraph', 'content' => [self::text($text, $marks)]];
    }

    /**
     * One paragraph per line: the model has no line break inside a paragraph.
     *
     * @param iterable<string|null> $lines Empty and null lines are skipped.
     * @return list<array>
     */
    public static function paragraphs(iterable $lines): array
    {
        $paragraphs = [];

        foreach ($lines as $line) {
            if (null !== $line && '' !== $line) {
                $paragraphs[] = self::paragraph($line);
            }
        }

        return $paragraphs;
    }

    /**
     * @param list<array> $marks
     */
    public static function text(
        string $text,
        array $marks = []
    ): array {
        return [] === $marks ? ['type' => 'text', 'text' => $text] : ['type' => 'text', 'text' => $text, 'marks' => $marks];
    }

    public static function bold(): array
    {
        return ['type' => 'bold'];
    }

    public static function strike(): array
    {
        return ['type' => 'strike'];
    }

    /**
     * @param list<list<array>> $rows Each row a list of cells.
     */
    public static function table(array $rows): array
    {
        return [
            'type' => 'table',
            'content' => array_map(fn (array $cells) => ['type' => 'tableRow', 'content' => $cells], $rows),
        ];
    }

    /**
     * @param list<array> $blocks
     */
    public static function cell(
        array $blocks,
        ?string $align = null,
        int $colspan = 1
    ): array {
        return self::withCellAttrs(['type' => 'tableCell', 'content' => $blocks], $align, $colspan);
    }

    public static function textCell(
        string $text,
        ?string $align = null,
        int $colspan = 1
    ): array {
        return self::cell([self::paragraph($text)], $align, $colspan);
    }

    /**
     * A header cell: a row made of header cells only is repeated on every page a table runs onto.
     */
    public static function header(
        string $text,
        ?string $align = null,
        int $colspan = 1
    ): array {
        return self::withCellAttrs(['type' => 'tableHeader', 'content' => [self::paragraph($text)]], $align, $colspan);
    }

    /**
     * Data a machine reads (an EPC payment request…), drawn by the engine.
     */
    public static function qrCode(
        string $value,
        ?string $alt = null
    ): array {
        return ['type' => 'qrCode', 'attrs' => array_filter(['value' => $value, 'alt' => $alt])];
    }

    private static function withCellAttrs(
        array $cell,
        ?string $align,
        int $colspan
    ): array {
        $attrs = array_filter(['align' => $align, 'colspan' => $colspan > 1 ? $colspan : null]);

        return [] === $attrs ? $cell : [...$cell, 'attrs' => $attrs];
    }
}
