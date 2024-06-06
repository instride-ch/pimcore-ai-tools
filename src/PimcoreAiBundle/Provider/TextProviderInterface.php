<?php

namespace Instride\Bundle\PimcoreAiBundle\Provider;

interface TextProviderInterface
{
    public function getText(array $options): mixed;

}
