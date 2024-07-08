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

namespace Instride\Bundle\PimcoreAiToolsBundle\Model\AiFrontendConfiguration\Listing;

use Doctrine\DBAL\Exception;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiFrontendConfiguration;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiFrontendConfiguration\Listing;
use Pimcore\Model;

/**
 * @property Listing $model
 */
final class Dao extends Model\Listing\Dao\AbstractDao
{
    protected string $tableName = 'pimcore_ai_tools_frontend_configuration';

    /**
     * @return AiFrontendConfiguration[]
     * @throws Exception
     */
    public function load(): array
    {
        $frontendConfigurationsData = $this->db->fetchFirstColumn('SELECT id FROM ' .$this->tableName . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        $frontendConfigurations = [];
        foreach ($frontendConfigurationsData as $frontendConfigurationData) {
            $frontendConfigurations[] = AiFrontendConfiguration::getById($frontendConfigurationData);
        }

        $this->model->setFrontendConfigurations($frontendConfigurations);

        return $frontendConfigurations;
    }

    /**
     * @return list<array<string,mixed>>
     * @throws Exception
     */
    public function getDataArray(): array
    {
        $frontendConfigurationsData = $this->db->fetchAllAssociative('SELECT * FROM '. $this->tableName . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        return $frontendConfigurationsData;
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
