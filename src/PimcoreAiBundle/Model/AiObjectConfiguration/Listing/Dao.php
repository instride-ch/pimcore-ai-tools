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

namespace Instride\Bundle\PimcoreAiBundle\Model\AiObjectConfiguration\Listing;

use Doctrine\DBAL\Exception;
use Instride\Bundle\PimcoreAiBundle\Model\AiObjectConfiguration;
use Instride\Bundle\PimcoreAiBundle\Model\AiObjectConfiguration\Listing;
use Pimcore\Model;

/**
 * @property Listing $model
 */
final class Dao extends Model\Listing\Dao\AbstractDao
{
    protected string $tableName = 'pimcore_ai_object_configuration';

    /**
     * Loads a list of static routes for the specicifies parameters, returns an array of Staticroute elements
     *
     * @return AiObjectConfiguration[]
     * @throws Exception
     */
    public function load(): array
    {
        $objectConfigurationsData = $this->db->fetchFirstColumn('SELECT id FROM ' .$this->tableName . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        $objectConfigurations = [];
        foreach ($objectConfigurationsData as $objectConfigurationData) {
            $objectConfigurations[] = AiObjectConfiguration::getById($objectConfigurationData);
        }

        $this->model->setObjectConfigurations($objectConfigurations);

        return $objectConfigurations;
    }

    /**
     * @return list<array<string,mixed>>
     * @throws Exception
     */
    public function getDataArray(): array
    {
        $objectConfigurationsData = $this->db->fetchAllAssociative('SELECT * FROM '. $this->tableName . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        return $objectConfigurationsData;
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
