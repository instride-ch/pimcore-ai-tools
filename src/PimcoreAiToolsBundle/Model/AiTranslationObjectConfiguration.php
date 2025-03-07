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

namespace Instride\Bundle\PimcoreAiToolsBundle\Model;

use Instride\Bundle\PimcoreAiToolsBundle\Model\AiTranslationObjectConfiguration\Dao;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\Exception\NotFoundException;

/**
 * @method Dao getDao()
 * @method void delete()
 * @method void save()
 */
final class AiTranslationObjectConfiguration extends AbstractModel
{
    private ?int $id = null;

    private ?string $className = null;

    private ?string $fields = null;

    private ?string $standardLanguage = null;

    private ?string $provider = null;


    public static function getById(int $id): ?self
    {
        try {
            $obj = new self;
            $obj->setId($id);
            $obj->getDao()->getById();

            return $obj;
        }
        catch (NotFoundException $e) {
            return null;
        }
    }

    public static function create(): self
    {
        $obj = new self();
        $obj->save();

        return $obj;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    public function getFields(): ?string
    {
        return $this->fields;
    }

    public function setFields(string $fields): void
    {
        $this->fields = $fields;
    }

    public function getStandardLanguage(): ?string
    {
        return $this->standardLanguage;
    }

    public function setStandardLanguage(?string $standardLanguage): void
    {
        $this->standardLanguage = $standardLanguage;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): void
    {
        $this->provider = $provider;
    }

    public function getData(): array
    {
        return [
            'id' => $this->getId(),
            'className' => $this->getClassName(),
            'fields' => $this->getFields(),
            'standardLanguage' => $this->getStandardLanguage(),
            'provider' => $this->getProvider(),
        ];
    }

    public static function hasExistingClass(string $className): bool
    {
        $list = new AiTranslationObjectConfiguration\Listing();
        $list->setCondition("className = ?", [$className]);
        $list->setLimit(1);
        $entries = $list->load();

        return count($entries) > 0;
    }

}
