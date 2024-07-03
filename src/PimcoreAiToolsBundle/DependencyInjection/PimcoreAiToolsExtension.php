<?php

declare(strict_types=1);

/**
 * Pimcore Monitor
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  2024 instride AG (https://instride.ch)
 */

namespace Instride\Bundle\PimcoreAiToolsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class PimcoreAiToolsExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Register parameters
        $editables = [];
        foreach ($config as $configName => $subConfig) {
            if ($configName === 'providers') {
                foreach ($subConfig as $subConfigName => $subSubConfig) {
                    foreach ($subSubConfig as $subSubConfigName => $confValue) {
                        $container->setParameter(
                            \sprintf('pimcore_ai.%s.%s.%s', $configName, $subConfigName, $subSubConfigName),
                            $confValue
                        );
                    }
                }
            }
            if ($configName === 'editables') {
                foreach ($subConfig as $subConfigName => $subSubConfig) {
                    $editables[$subConfigName] = $subSubConfig;
                }
            }
        }

        $container->setParameter('pimcore_ai.editables', \json_encode($editables, JSON_THROW_ON_ERROR));
    }
}
