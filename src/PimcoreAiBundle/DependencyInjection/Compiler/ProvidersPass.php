<?php

namespace Instride\Bundle\PimcoreAiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProvidersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $controllerServiceDefinition = $container->findDefinition('Instride\Bundle\PimcoreAiBundle\Services\AiService');
        $providers = $container->findTaggedServiceIds('pimcore_ai.provider_type');
        $controllerServiceDefinition->addMethodCall('setProviders', [\array_keys($providers)]);
    }
}
