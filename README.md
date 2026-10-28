# symfony-remote-pdf-factory

Version: 2.0.5

Client of a pdf-factory service: semantic documents and their charter posted, PDF back, declared as a symfony-remote

## Table of Contents

- [Installation](#installation)
- [Introduction](#introduction)

## Installation

```php
// config/bundles.php
Wexample\SymfonyRemote\WexampleSymfonyRemoteBundle::class => ['all' => true],
Wexample\SymfonyRemotePdfFactory\WexampleSymfonyRemotePdfFactoryBundle::class => ['all' => true],
```

```yaml
# config/packages/wexample_symfony_remote_pdf_factory.yaml
wexample_symfony_remote_pdf_factory:
  url: '%env(default::PDF_FACTORY_URL)%'   # e.g. http://pdf_factory:8000
```

Without a URL the remote reports itself unconfigured and `render()` throws.

`symfony-remote-pdf-factory` is the Symfony client of a pdf-factory service: it posts a semantic document (ProseMirror / TipTap JSON, what each part *is*) with the charter that dresses it, and gets the PDF back. `PdfFactoryClient` is declared as a `symfony-remote`, so `remote:status` checks the service; `PdfTheme` carries the charter (stylesheet, blocks, variables); `DocumentNodeHelper` builds the nodes of the model.

The package knows no document of its own: invoices, gift cards or tax forms are built by the packages that own them, and dressed by the application.
