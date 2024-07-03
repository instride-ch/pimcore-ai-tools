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

namespace Instride\Bundle\PimcoreAiToolsBundle\Model\AiDefaultsConfiguration;

use Instride\Bundle\PimcoreAiToolsBundle\Model\AiDefaultsConfiguration;
use Pimcore\Model\Dao\AbstractDao;
use Pimcore\Model\Exception\NotFoundException;

/**
 * @property AiDefaultsConfiguration $model
 */
class Dao extends AbstractDao
{
    protected string $tableName = 'pimcore_ai_tools_defaults_configuration';

    public function getById(?int $id = null): void
    {
        if ($id !== null)  {
            $this->model->setId($id);
        }

        $data = $this->db->fetchAssociative('SELECT * FROM '.$this->tableName.' WHERE id = ?', [$this->model->getId()]);

        if (!$data) {
            throw new NotFoundException(\sprintf('Unable to load defaults configuration with ID `%s`', $this->model->getId()));
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
