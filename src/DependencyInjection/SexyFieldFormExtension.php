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
            $loader->load('service/services.yml');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'sexy_field_form';
    }
}
