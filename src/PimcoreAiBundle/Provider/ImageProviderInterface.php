<?php

namespace Instride\Bundle\PimcoreAiBundle\Provider;

interface ImageProviderInterface
{
    public function getImage(array $options): mixed;
}
