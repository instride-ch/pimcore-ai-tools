<?php

namespace Instride\Bundle\PimcoreAiToolsBundle\Provider;

interface ProviderInterface
{
    public static function getName(): string;

    public function getClient(): mixed;
}
