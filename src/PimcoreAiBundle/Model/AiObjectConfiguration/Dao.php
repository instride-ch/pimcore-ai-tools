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

use Doctrine\DBAL\Exception;
use Pimcore\Model\Dao\AbstractDao;
use Pimcore\Model\Exception\NotFoundException;

class Dao extends AbstractDao
{
    protected string $tableName = 'pimcore_ai_object_configuration';

    /**
     * get object configuration by id
     *
     * @param int|null $id
     *
     * @throws Exception
     */
    public function getById(?int $id = null): void
    {
        if ($id !== null)  {
            $this->model->setId($id);
        }

        $data = $this->db->fetchAssociative('SELECT * FROM '.$this->tableName.' WHERE id = ?', [$this->model->getId()]);

        if (!$data) {
            throw new NotFoundException("Object with the ID " . $this->model->getId() . " doesn't exists");
        }

        $this->assignVariablesToModel($data);
    }

    public function save(): void
    {
        $vars = \get_object_vars($this->model);

        $buffer = [];

        $validColumns = $this->getValidTableColumns($this->tableName);

        if (\count($vars)) {
            foreach ($vars as $k => $v) {
                if (!\in_array($k, $validColumns)) {
                    continue;
                }

                $getter = "get" . \ucfirst($k);

                if (!\is_callable([$this->model, $getter])) {
                    continue;
                }

                $value = $this->model->$getter();

                if (\is_bool($value)) {
                    $value = (int)$value;
                }

                $buffer[$k] = $value;
            }
        }

        if ($this->model->getId() !== null) {
            $this->db->update($this->tableName, $buffer, ["id" => $this->model->getId()]);
            return;
        }

        $this->db->insert($this->tableName, $buffer);
        $this->model->setId($this->db->lastInsertId());
    }

    public function delete(): void
    {
        $this->db->delete($this->tableName, ["id" => $this->model->getId()]);
    }

    /**
     * @throws \Exception
     */
    public function update(): void
    {
        $data = [];
        $type = $this->model->getObjectVars();

        foreach ($type as $key => $value) {
            if (\in_array($key, $this->getValidTableColumns($this->tableName), true)) {
                if (\is_bool($value)) {
                    $value = (int) $value;
                }
                $data[$key] = $value;
            }
        }

        $this->db->update($this->tableName, $data, ['id' => $this->model->getId()]);
    }

}
