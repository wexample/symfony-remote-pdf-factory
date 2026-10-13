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
