`symfony-remote-pdf-factory` is the Symfony client of a pdf-factory service: it posts a semantic document (ProseMirror / TipTap JSON, what each part *is*) with the charter that dresses it, and gets the PDF back. `PdfFactoryClient` is declared as a `symfony-remote`, so `remote:status` checks the service; `PdfTheme` carries the charter (stylesheet, blocks, variables); `DocumentNodeHelper` builds the nodes of the model.

The package knows no document of its own: invoices, gift cards or tax forms are built by the packages that own them, and dressed by the application.
