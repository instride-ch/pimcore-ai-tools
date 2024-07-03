<?php

declare(strict_types=1);

/**
 * Pimcore AI
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2024 instride AG (https://instride.ch)
 * @license    https://github.com/instride-ch/PimcoreAiToolsBundle?tab=GPL-3.0-1-ov-file#readme GNU General Public License version 3 (GPLv3)
 */

namespace Instride\Bundle\PimcoreAiToolsBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;
use Pimcore\Extension\Bundle\Traits\BundleAdminClassicTrait;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

class PimcoreAiToolsBundle extends AbstractPimcoreBundle implements PimcoreBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;
    use PackageVersionTrait;

    /**
     * {@inheritDoc}
     */
    public function getNiceName(): string
    {
        return 'Pimcore AI-Tools Bundle';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return 'AI-Tools for Pimcore';
    }

    public function getCssPaths(): array
    {
        return [
          '/bundles/pimcoreaitools/css/pimcore/admin.css',
          '/bundles/pimcoreaitools/css/pimcore/icons.css',
        ];
    }

    public function getJsPaths(): array
    {
        return [
          '/bundles/pimcoreaitools/js/pimcore/startup.js',
          '/bundles/pimcoreaitools/js/pimcore/settings.js',
          '/bundles/pimcoreaitools/js/pimcore/object/classes/data/aiWysiwyg.js',
          '/bundles/pimcoreaitools/js/pimcore/object/tags/aiWysiwyg.js',
        ];
    }

    public function getEditmodeCssPaths(): array
    {
        return [
          '/bundles/pimcoreaitools/css/pimcore/editmode.css',
          '/bundles/pimcoreaitools/css/pimcore/icons.css',
        ];
    }

    public function getEditmodeJsPaths(): array
    {
        return [
          '/bundles/pimcoreaitools/js/pimcore/document/editables/aiWysiwyg.js',
        ];
    }
}
