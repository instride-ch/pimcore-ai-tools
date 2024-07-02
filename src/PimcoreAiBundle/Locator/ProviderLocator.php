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

namespace Instride\Bundle\PimcoreAiBundle\Locator;

use Instride\Bundle\PimcoreAiBundle\Provider\ImageProviderInterface;
use Instride\Bundle\PimcoreAiBundle\Provider\TextProviderInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class ProviderLocator
{
    private ContainerInterface $locator;

    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getProvider(string $providerName)
    {
        if (!$this->locator->has($providerName)) {
            throw new \InvalidArgumentException(sprintf('The provider "%s" is not defined.', $providerName));
        }

        return $this->locator->get($providerName);
    }

    public function getTextProviders()
    {
        $textProviders = [];
        foreach (\array_keys($this->locator->getProvidedServices()) as $providerName) {
            $provider = $this->locator->get($providerName);
            if ($provider instanceof TextProviderInterface) {
                $textProviders[] = $providerName;
            }
        }

        return $textProviders;
    }

    public function getImageProviders()
    {
        $imageProviders = [];
        foreach (\array_keys($this->locator->getProvidedServices()) as $providerName) {
            $provider = $this->locator->get($providerName);
            if ($provider instanceof ImageProviderInterface) {
                $imageProviders[] = $providerName;
            }
        }

        return $imageProviders;
    }

    public function getAllProviders(): array
    {
        return \array_keys($this->locator->getProvidedServices());
    }
}
