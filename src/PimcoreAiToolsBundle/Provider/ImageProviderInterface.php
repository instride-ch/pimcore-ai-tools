<?php

namespace Instride\Bundle\PimcoreAiToolsBundle\Provider;

interface ImageProviderInterface
{
    public function getImage(array $options): mixed;
}
