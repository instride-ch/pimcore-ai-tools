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

use Exception;
use Instride\Bundle\PimcoreAiBundle\Model\AiObjectConfiguration;
use Pimcore\Model\Listing;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineQueryBuilder;
use Pimcore\Model\Listing\Dao\QueryBuilderHelperTrait;

final class Dao extends Listing\Dao\AbstractDao
{
    use QueryBuilderHelperTrait;

    protected string $tableName = 'pimcore_ai_object_configuration';

    /**
     * Get tableName, either for localized or non-localized data.
     *
     * @throws \Exception
     */
    protected function getTableName(): string
    {
        return $this->tableName;
    }

    public function getQueryBuilder(): DoctrineQueryBuilder
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $field = $this->getTableName().'.id';
        $queryBuilder->select(\sprintf('SQL_CALC_FOUND_ROWS %s as id', $field));
        $queryBuilder->from($this->getTableName());

        $this->applyListingParametersToQueryBuilder($queryBuilder);

        return $queryBuilder;
    }

    /**
     * Loads objects from the database.
     *
     * @return AiObjectConfiguration[]
     * @throws Exception
     */
    public function load(): array
    {
        $list = $this->loadIdList();

        $objects = [];
        foreach ($list as $id) {
            if ($object = AiObjectConfiguration::getById($id)) {
                $objects[] = $object;
            }
        }

        $this->model->setData($objects);

        return $objects;
    }

    /**
     * Loads a list for the specicifies parameters, returns an array of ids.
     *
     * @return int[]
     * @throws \Exception
     */
    public function loadIdList(): array
    {
        $query = $this->getQueryBuilder();
        $objectIds = $this->db->fetchFirstColumn($query->getSQL(), $query->getParameters(), $query->getParameterTypes());
        $this->totalCount = (int) $this->db->fetchOne('SELECT FOUND_ROWS()');

        return \array_map('intval', $objectIds);
    }

    public function getCount(): int
    {
        if ($this->model->isLoaded()) {
            return \count($this->model->getData());
        }

        $idList = $this->loadIdList();

        return \count($idList);
    }

    public function getTotalCount(): int
    {
        $queryBuilder = $this->getQueryBuilder();
        $this->prepareQueryBuilderForTotalCount($queryBuilder, $this->getTableName() . '.id');

        $totalCount = $this->db->fetchOne($queryBuilder->getSql(), $queryBuilder->getParameters(), $queryBuilder->getParameterTypes());

        return (int) $totalCount;
    }
}
