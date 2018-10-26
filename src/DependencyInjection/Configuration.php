<?php

namespace Tardigrades\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('sexy_field_form');
        $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->useAttributeAsKey('name')
            ->prototype('variable')
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
