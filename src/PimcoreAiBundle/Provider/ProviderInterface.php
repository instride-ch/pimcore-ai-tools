<?php

namespace Instride\Bundle\PimcoreAiBundle\Provider;

interface ProviderInterface
{
    public static function getName(): string;

    public function getClient(): mixed;
}
