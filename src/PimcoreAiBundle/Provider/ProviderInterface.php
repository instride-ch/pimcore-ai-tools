<?php

namespace Instride\Bundle\PimcoreAiBundle\Provider;

interface ProviderInterface
{
    public function getClient(): mixed;

    public function getText(array $options): mixed;

    public function getImage(array $options): mixed;
}
