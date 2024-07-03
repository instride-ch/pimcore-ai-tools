<?php

namespace Instride\Bundle\PimcoreAiToolsBundle\Provider;

interface TextProviderInterface
{
    public function getText(array $options): string;

}
