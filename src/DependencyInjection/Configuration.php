<?php

namespace Wexample\SymfonyRemotePdfFactory\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wexample_symfony_remote_pdf_factory');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('url')->defaultNull()->info('Base URL of a pdf-factory service, e.g. http://pdf_factory:8000.')->end()
            ->end();

        return $treeBuilder;
    }
}
