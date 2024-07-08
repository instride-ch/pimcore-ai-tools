<?php

declare(strict_types=1);

/**
 * instride.
 *
 * LICENSE
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that is distributed with this source code.
 *
 * @copyright Copyright (c) 2024 instride AG (https://instride.ch)
 */

namespace Instride\Bundle\PimcoreAiToolsBundle\Model\AiFrontendConfiguration;

use Instride\Bundle\PimcoreAiToolsBundle\Model\AiFrontendConfiguration;
use Pimcore\Model\Listing\AbstractListing;

/**
 * @method Listing\Dao getDao()
 * @method AiFrontendConfiguration[] load()
 * @method AiFrontendConfiguration|false current()
 * @method int getTotalCount()
 * @method list<array<string,mixed>> getDataArray()
 */
final class Listing extends AbstractListing
{
    /**
     * @return AiFrontendConfiguration[]
     */
    public function getFrontendConfigurations(): array
    {
        return $this->getData();
    }

    /**
     * @param AiFrontendConfiguration[]|null $frontendConfigurations
     *
     * @return $this
     */
    public function setFrontendConfigurations(?array $frontendConfigurations): static
    {
        return $this->setData($frontendConfigurations);
    }
}
