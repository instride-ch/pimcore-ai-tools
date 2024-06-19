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
use Pimcore\Model\Dao\AbstractDao;
use Pimcore\Model\Exception\NotFoundException;

/**
 * @property AiEditableConfiguration $model
 */
class Dao extends AbstractDao
{
    protected string $tableName = 'pimcore_ai_editable_configuration';

    /**
     * Get the data for the object from database for the given id, or from the ID which is set in the object
     *
     *
     * @throws NotFoundException
     */
    public function getById(?int $id = null): void
    {
        if ($id !== null)  {
            $this->model->setId($id);
        }

        $data = $this->db->fetchAssociative('SELECT * FROM '.$this->tableName.' WHERE id = ?', [$this->model->getId()]);

        if (!$data) {
            throw new NotFoundException(\sprintf('Unable to load editable configuration with ID `%s`', $this->model->getId()));
        }

        $this->assignVariablesToModel($data);
    }

    public function save(): void
    {
        if (!$this->model->getId()) {
            $this->create();
        }

        $this->update();
    }

    public function delete(): void
    {
        $this->db->delete($this->tableName, ['id' => $this->model->getId()]);
    }

    /**
     * @throws \Exception
     */
    public function update(): void
    {
        $data = [];
        $type = $this->model->getData();

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

    public function create(): void
    {
        $this->db->insert($this->tableName, []);

        $this->model->setId((int) $this->db->lastInsertId());
    }
}
