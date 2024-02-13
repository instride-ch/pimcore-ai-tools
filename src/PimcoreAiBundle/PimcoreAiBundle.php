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
 * @license    https://github.com/instride-ch/PimcoreAiBundle?tab=GPL-3.0-1-ov-file#readme GNU General Public License version 3 (GPLv3)
 */

namespace Instride\Bundle\PimcoreAiBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

class PimcoreAiBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    /**
     * {@inheritDoc}
     */
    public function getNiceName(): string
    {
        return 'Pimcore Ai Bundle';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return 'AI Bundle for Pimcore';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackageName(): string
    {
        return 'instride/pimcore-ai-bundle';
    }
}
