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

namespace Instride\Bundle\PimcoreAiBundle\Model\AiEditableConfiguration\Listing;

use Doctrine\DBAL\Exception;
use Instride\Bundle\PimcoreAiBundle\Model\AiEditableConfiguration;
use Instride\Bundle\PimcoreAiBundle\Model\AiEditableConfiguration\Listing;
use Pimcore\Model;

/**
 * @property Listing $model
 */
final class Dao extends Model\Listing\Dao\AbstractDao
{
    protected string $tableName = 'pimcore_ai_editable_configuration';

    /**
     * @return AiEditableConfiguration[]
     * @throws Exception
     */
    public function load(): array
    {
        $editableConfigurationsData = $this->db->fetchFirstColumn('SELECT id FROM ' .$this->tableName . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        $editableConfigurations = [];
        foreach ($editableConfigurationsData as $editableConfigurationData) {
            $editableConfigurations[] = AiEditableConfiguration::getById($editableConfigurationData);
        }

        $this->model->setEditableConfigurations($editableConfigurations);

        return $editableConfigurations;
    }

    /**
     * @return list<array<string,mixed>>
     * @throws Exception
     */
    public function getDataArray(): array
    {
        $editableConfigurationsData = $this->db->fetchAllAssociative('SELECT * FROM '. $this->tableName . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        return $editableConfigurationsData;
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
