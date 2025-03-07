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

namespace Instride\Bundle\PimcoreAiToolsBundle\Model\AiTranslationObjectConfiguration;

use Instride\Bundle\PimcoreAiToolsBundle\Model\AiTranslationObjectConfiguration;
use Pimcore\Model\Listing\AbstractListing;

/**
 * @method Listing\Dao getDao()
 * @method AiTranslationObjectConfiguration[] load()
 * @method AiTranslationObjectConfiguration|false current()
 * @method int getTotalCount()
 * @method list<array<string,mixed>> getDataArray()
 */
final class Listing extends AbstractListing
{
    /**
     * @return AiTranslationObjectConfiguration[]
     */
    public function getTranslationObjectConfigurations(): array
    {
        return $this->getData();
    }

    /**
     * @param AiTranslationObjectConfiguration[]|null $translationObjectConfigurations
     *
     * @return $this
     */
    public function setTranslationObjectConfigurations(?array $translationObjectConfigurations): static
    {
        return $this->setData($translationObjectConfigurations);
    }
}
