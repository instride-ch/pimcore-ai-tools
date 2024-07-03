<?php

declare(strict_types=1);

/**
 * instride AG.
 *
 * LICENSE
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that is distributed with this source code.
 *
 * @copyright  Copyright (c) 2024 instride AG (https://instride.ch)
 */

namespace Instride\Bundle\PimcoreAiToolsBundle\Services;

use Instride\Bundle\PimcoreAiToolsBundle\Locator\ProviderLocator;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiDefaultsConfiguration;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiObjectConfiguration;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiEditableConfiguration;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

final class ConfigurationService
{
    private ProviderLocator $providerLocator;
    private ?array $defaultConfiguration;

    public function __construct(ProviderLocator $providerLocator)
    {
        $this->providerLocator = $providerLocator;
        $this->defaultConfiguration = AiDefaultsConfiguration::getById(1)?->getData();
    }

    public function getObjectConfiguration(string $className, string $fieldName, string $type): array
    {
        $list = new AiObjectConfiguration\Listing();
        $list->setCondition('`className` LIKE "%' . $className . '%" AND `fieldName` LIKE "%' . $fieldName . '%" AND `type` = "' . $type . '"');
        $list->load();

        $configuration = $list->getData()[0]->getData();
        $key = 'object' . \implode('', \array_map('ucfirst', \explode('_', $type)));

        if (empty($configuration['provider'])) {
            $configuration['provider'] = $this->defaultConfiguration['textProvider'];
        }

        if (empty($configuration['prompt'])) {
            $configuration['prompt'] = $this->defaultConfiguration[$key];
        }

        $provider = $this->providerLocator->getProvider($configuration['provider']);

        return [
            'provider' => $provider,
            'prompt' => $configuration['prompt'],
            'options' => $configuration['options'],
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getEditableConfiguration(string $areabrick, string $editable, string $type): array
    {
        $list = new AiEditableConfiguration\Listing();
        $list->setCondition('`areabrick` LIKE "%' . $areabrick . '%" AND `editable` LIKE "%' . $editable . '%" AND `type` = "' . $type . '"');
        $list->load();

        $configuration = $list->getData()[0]->getData();
        $key = 'editable' . \implode('', \array_map('ucfirst', \explode('_', $type)));

        if (empty($configuration['provider'])) {
            $configuration['provider'] = $this->defaultConfiguration['textProvider'];
        }

        if (empty($configuration['prompt'])) {
            $configuration['prompt'] = $this->defaultConfiguration[$key];
        }

        $provider = $this->providerLocator->getProvider($configuration['provider']);

        return [
            'provider' => $provider,
            'prompt' => $configuration['prompt'],
            'options' => $configuration['options'],
        ];
    }
}
