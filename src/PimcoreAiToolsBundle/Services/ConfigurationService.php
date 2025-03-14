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
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiEditableConfiguration;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiFrontendConfiguration;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiObjectConfiguration;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiTranslationObjectConfiguration;
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
            // Get default provider if not set
            $configuration['provider'] = $this->defaultConfiguration['textProvider'];
        }

        if (empty($configuration['prompt'])) {
            // Get default prompt if not set
            $configuration['prompt'] = $this->defaultConfiguration[$key];
        }

        // Get provider
        $provider = $this->providerLocator->getProvider($configuration['provider']);

        // Convert options from string to array
        $optionsArray = $this->getOptionsArray($configuration['options']);

        return [
            'provider' => $provider,
            'prompt' => $configuration['prompt'],
            'options' => $optionsArray,
        ];
    }

    public function getTranslationObjectConfiguration(string $className): ?array
    {
        $list = new AiTranslationObjectConfiguration\Listing();
        $list->setCondition('`className` LIKE "%' . $className . '%"');
        $list->load();

        if (empty($list->getData())) {
            return null;
        }

        $configuration = $list->getData()[0]->getData();

        if (empty($configuration['provider'])) {
            $configuration['provider'] = $this->defaultConfiguration['textProvider'];
        }

        $provider = $this->providerLocator->getProvider($configuration['provider']);

        return [
            'provider' => $provider,
            'fields' => $configuration['fields'],
            'standardLanguage' => $configuration['standardLanguage'],
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
            // Get default provider if not set
            $configuration['provider'] = $this->defaultConfiguration['textProvider'];
        }

        if (empty($configuration['prompt'])) {
            // Get default prompt if not set
            $configuration['prompt'] = $this->defaultConfiguration[$key];
        }

        // Get provider
        $provider = $this->providerLocator->getProvider($configuration['provider']);

        // Convert options from string to array
        $optionsArray = $this->getOptionsArray($configuration['options']);

        return [
            'provider' => $provider,
            'prompt' => $configuration['prompt'],
            'options' => $optionsArray,
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFrontendConfiguration(string $name, string $type, bool $isRefineText = false): array
    {
        $list = new AiFrontendConfiguration\Listing();
        $list->setCondition('`name` LIKE "%' . $name . '%" AND `type` = "' . $type . '"');
        $list->load();

        $configuration = $list->getData()[0]->getData();
        $key = 'frontend' . \implode('', \array_map('ucfirst', \explode('_', $type)));

        if (empty($configuration['provider'])) {
            // Get default provider if not set
            $configuration['provider'] = $this->defaultConfiguration['textProvider'];
        }

        if (empty($configuration['prompt'])) {
            if ($isRefineText) {
                $configuration['prompt'] = '';
            } else {
                $configuration['prompt'] = $this->defaultConfiguration[$key] ?? '';
            }
        }

        // Get provider
        $provider = $this->providerLocator->getProvider($configuration['provider']);

        // Convert options from string to array
        $optionsArray = $this->getOptionsArray($configuration['options']);

        return [
            'provider' => $provider,
            'prompt' => $configuration['prompt'],
            'options' => $optionsArray,
        ];
    }

    private function getOptionsArray(?string $optionsString = null): ?array
    {
        if (!$optionsString) {
            return null;
        }

        $optionsArray = [];
        $options = \array_map('trim', \explode(';', $optionsString));
        foreach($options as $option) {
            if (empty($option)) {
                continue;
            }

            $parts = \array_map('trim', \explode(':', $option));
            $optionsArray[$parts[0]] = $parts[1];
        }

        return $optionsArray;
    }
}
