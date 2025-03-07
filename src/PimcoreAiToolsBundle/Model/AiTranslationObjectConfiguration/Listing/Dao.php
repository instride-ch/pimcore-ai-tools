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

namespace Instride\Bundle\PimcoreAiToolsBundle\Model\AiTranslationObjectConfiguration\Listing;

use Doctrine\DBAL\Exception;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiTranslationObjectConfiguration;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiTranslationObjectConfiguration\Listing;
use Pimcore\Model;

/**
 * @property Listing $model
 */
final class Dao extends Model\Listing\Dao\AbstractDao
{
    protected string $tableName = 'pimcore_ai_tools_translation_object_configuration';

    /**
     * @return AiTranslationObjectConfiguration[]
     * @throws Exception
     */
    public function load(): array
    {
        $translationsObjectConfigurationsData = $this->db->fetchFirstColumn('SELECT id FROM ' .$this->tableName . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        $translationObjectConfigurations = [];
        foreach ($translationsObjectConfigurationsData as $translationsObjectConfigurationData) {
            $translationObjectConfigurations[] = AiTranslationObjectConfiguration::getById($translationsObjectConfigurationData);
        }

        $this->model->setTranslationObjectConfigurations($translationObjectConfigurations);

        return $translationObjectConfigurations;
    }

    /**
     * @return list<array<string,mixed>>
     * @throws Exception
     */
    public function getDataArray(): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM '. $this->tableName . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());
    }

    public function getTotalCount(): int
    {
        try {
            return (int) $this->db->fetchOne('SELECT COUNT(*) FROM ' . $this->tableName . ' ' . $this->getCondition(), $this->model->getConditionVariables());
        } catch (\Exception $e) {
            return 0;
        }
    }
}
