<?php

namespace Instride\Bundle\PimcoreAiBundle\Provider;

interface ProviderInterface
{
    public function getClient(): mixed;
}
