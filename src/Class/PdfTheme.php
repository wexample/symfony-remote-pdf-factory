<?php

namespace Wexample\SymfonyRemotePdfFactory\Class;

/**
 * A charter, posted beside each document: what it wears, never what it says.
 *
 * - stylesheet: CSS served after the engine's default sheet; fonts and images in it are data URIs;
 * - blocks: document fragments taken out of the flow under names the stylesheet places (a logo, a notice);
 * - variables: words for margin boxes, around the page counters.
 */
class PdfTheme
{
    /**
     * @param array<string, list<array>> $blocks
     * @param array<string, string>      $variables
     */
    public function __construct(
        public readonly string $stylesheet = '',
        public readonly array $blocks = [],
        public readonly array $variables = [],
    ) {
    }

    /**
     * One stylesheet out of several files, in order: a base charter, then what overrides it.
     *
     * @param list<string>          $paths
     * @param array<string, string> $variables
     */
    public static function fromFiles(
        array $paths,
        array $variables = []
    ): self {
        return new self(
            implode("\n\n", array_map(fn (string $path) => (string) file_get_contents($path), $paths)),
            variables: $variables,
        );
    }

    public function withBlock(
        string $name,
        array $content
    ): self {
        return new self($this->stylesheet, [...$this->blocks, $name => $content], $this->variables);
    }

    public function withVariables(array $variables): self
    {
        return new self($this->stylesheet, $this->blocks, [...$this->variables, ...$variables]);
    }

    public function withStylesheet(string $stylesheet): self
    {
        return new self($this->stylesheet."\n\n".$stylesheet, $this->blocks, $this->variables);
    }

    public function toArray(): array
    {
        return array_filter([
            'stylesheet' => $this->stylesheet,
            'blocks' => $this->blocks,
            'variables' => $this->variables,
        ]);
    }
}
