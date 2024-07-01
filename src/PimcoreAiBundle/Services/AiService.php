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

namespace Instride\Bundle\PimcoreAiBundle\Services;

use Instride\Bundle\PimcoreAiBundle\Provider\ImageProviderInterface;
use Instride\Bundle\PimcoreAiBundle\Provider\TextProviderInterface;
use ReflectionClass;
use ReflectionException;

final class AiService
{
    private array $textProviders = [];
    private array $imageProviders = [];

    /**
     * @throws ReflectionException
     */
    public function setProviders(array $providerNames): void
    {
        foreach ($providerNames as $providerName) {
            $provider = new ReflectionClass($providerName);

            if ($provider->implementsInterface(TextProviderInterface::class)) {
                $data = [
                    'class' => $provider->getName(),
                    'name' => $provider->newInstanceWithoutConstructor()->getName(),
                ];
                $this->textProviders[] = $data;
            }

            if ($provider->implementsInterface(ImageProviderInterface::class)) {
                $data = [
                    'class' => $provider->getName(),
                    'name' => $provider->newInstanceWithoutConstructor()->getName(),
                ];
                $this->imageProviders[] = $data;
            }
        }
    }

    public function getTextProviders(): array
    {
        return $this->textProviders;
    }

    public function getImageProviders(): array
    {
        return $this->imageProviders;
    }

    public function getText(
        TextProviderInterface $textProvider,
        string $prompt,
        array $options = [],
    ): string
    {
        $options['prompt'] = $prompt;

        $response = $textProvider->getText($options);

        // ToDo: Error handling
        return $response['choices'][0]['message']['content'];
    }
}
