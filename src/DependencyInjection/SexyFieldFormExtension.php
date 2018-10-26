<?php

/*
 * This file is part of the SexyField package.
 *
 * (c) Dion Snoeijen <hallo@dionsnoeijen.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Tardigrades\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Tardigrades\DependencyInjection\Compiler\HTMLPurifierPass;
use Tardigrades\SectionField\Purifier\HTMLPurifiersRegistry;
use Tardigrades\SectionField\Purifier\HTMLPurifiersRegistryInterface;

class SexyFieldFormExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator([
                __DIR__.'/../config'
            ])
        );

        try {
            $loader->load('config.yml');
            $loader->load('service/services.yml');
        } catch (\Exception $exception) {
            throw $exception;
        }

        /* Prepend the default configuration. This cannot be defined within the
         * Configuration class, since the root node's children are array
         * prototypes.
         *
         * This cache path may be suppressed by either unsetting the "default"
         * configuration (relying on canBeUnset() on the prototype node) or
         * setting the "Cache.SerializerPath" option to null.
         */
        array_unshift($configs, [
            'default' => [
                'Cache.SerializerPath' => '%kernel.cache_dir%/htmlpurifier',
            ],
        ]);

        $configs = $this->processConfiguration(new Configuration(), $configs);

        $serializerPaths = [];

        foreach ($configs as $name => $config) {
            $configId = "exercise_html_purifier.config.$name";
            $configDefinition = $container->register($configId, \HTMLPurifier_Config::class)
                ->setPublic(false)
            ;
            if ('default' === $name) {
                $configDefinition
                    ->setFactory([\HTMLPurifier_Config::class, 'create'])
                    ->addArgument($config)
                ;
            } else {
                $configDefinition
                    ->setFactory([\HTMLPurifier_Config::class, 'inherit'])
                    ->addArgument(new Reference('exercise_html_purifier.config.default'))
                    ->addMethodCall('loadArray', [$config])
                ;
            }
            $container->register("tardigrades_html_purifier.$name", \HTMLPurifier::class)
                ->addArgument(new Reference($configId))
                ->addTag(HTMLPurifierPass::PURIFIER_TAG, ['profile' => $name])
            ;
            if (isset($config['Cache.SerializerPath'])) {
                $serializerPaths[] = $config['Cache.SerializerPath'];
            }
        }

        $container->register('tardigrades_html_purifier.purifiers_registry', HTMLPurifiersRegistry::class)
            ->setPublic(false);

        $container->setAlias(HTMLPurifiersRegistryInterface::class, 'tardigrades_html_purifier.purifiers_registry')
            ->setPublic(false);

        $container->setAlias(\HTMLPurifier::class, 'tardigrades_html_purifier.default')
            ->setPublic(false);

        $container->setParameter('tardigrades_html_purifier.cache_warmer.serializer.paths', array_unique($serializerPaths));
    }
}
