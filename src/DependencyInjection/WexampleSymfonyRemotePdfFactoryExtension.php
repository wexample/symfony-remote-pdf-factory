<?php

namespace Wexample\SymfonyRemotePdfFactory\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;

class WexampleSymfonyRemotePdfFactoryExtension extends AbstractWexampleSymfonyExtension
{
    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('wexample_symfony_remote_pdf_factory.url', $config['url']);

        $this->loadConfig(
            __DIR__,
            $container
        );
    }
}
