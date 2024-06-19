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

namespace Instride\Bundle\PimcoreAiBundle\Model\AiEditableConfiguration;

use Instride\Bundle\PimcoreAiBundle\Model\AiEditableConfiguration;
use Pimcore\Model\Listing\AbstractListing;

/**
 * @method Listing\Dao getDao()
 * @method AiEditableConfiguration[] load()
 * @method AiEditableConfiguration|false current()
 * @method int getTotalCount()
 * @method list<array<string,mixed>> getDataArray()
 */
final class Listing extends AbstractListing
{
    /**
     * @return AiEditableConfiguration[]
     */
    public function getEditableConfigurations(): array
    {
        return $this->getData();
    }

    /**
     * @param AiEditableConfiguration[]|null $editableConfigurations
     *
     * @return $this
     */
    public function setEditableConfigurations(?array $editableConfigurations): static
    {
        return $this->setData($editableConfigurations);
    }
}
