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

namespace Instride\Bundle\PimcoreAiBundle\Model\AiObjectConfiguration;

use Instride\Bundle\PimcoreAiBundle\Model\AiObjectConfiguration;
use Pimcore\Model\Listing\AbstractListing;


/**
 * @method Listing\Dao getDao()
 * @method AiObjectConfiguration[] load()
 * @method AiObjectConfiguration|false current()
 * @method int getTotalCount()
 * @method list<array<string,mixed>> getDataArray()
 */
final class Listing extends AbstractListing
{
    /**
     * @return AiObjectConfiguration[]
     */
    public function getObjectConfigurations(): array
    {
        return $this->getData();
    }

    /**
     * @param AiObjectConfiguration[]|null $objectConfigurations
     *
     * @return $this
     */
    public function setObjectConfigurations(?array $objectConfigurations): static
    {
        return $this->setData($objectConfigurations);
    }
}
