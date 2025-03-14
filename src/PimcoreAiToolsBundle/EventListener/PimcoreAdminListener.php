<?php

namespace Instride\Bundle\PimcoreAiToolsBundle\EventListener;

use Pimcore\Event\BundleManager\PathsEvent;

class PimcoreAdminListener
{
    public function addJSFiles(PathsEvent $event): void
    {
        $event->addPaths(['/bundles/pimcoreaitools/js/pimcore/object/translateSelect.js']);
    }
}
