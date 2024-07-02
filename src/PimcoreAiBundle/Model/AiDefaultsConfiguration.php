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

namespace Instride\Bundle\PimcoreAiBundle\Model;

use Instride\Bundle\PimcoreAiBundle\Model\AiDefaultsConfiguration\Dao;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\Exception\NotFoundException;

/**
 * @method Dao getDao()
 * @method void delete()
 * @method void save()
 */
class AiDefaultsConfiguration extends AbstractModel
{
    private ?int $id = null;

    private ?string $textProvider = null;

    private ?string $moduleTextCreation = null;

    private ?string $moduleTextOptimization = null;

    private ?string $moduleTextCorrection = null;

    private ?string $objectTextCreation = null;

    private ?string $objectTextOptimization = null;

    private ?string $objectTextCorrection = null;


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

    public function getTextProvider(): ?string
    {
        return $this->textProvider;
    }

    public function setTextProvider(?string $textProvider): void
    {
        $this->textProvider = $textProvider;
    }

    public function getModuleTextCreation(): ?string
    {
        return $this->moduleTextCreation;
    }

    public function setModuleTextCreation(?string $moduleTextCreation): void
    {
        $this->moduleTextCreation = $moduleTextCreation;
    }

    public function getModuleTextOptimization(): ?string
    {
        return $this->moduleTextOptimization;
    }

    public function setModuleTextOptimization(?string $moduleTextOptimization): void
    {
        $this->moduleTextOptimization = $moduleTextOptimization;
    }

    public function getModuleTextCorrection(): ?string
    {
        return $this->moduleTextCorrection;
    }

    public function setModuleTextCorrection(?string $moduleTextCorrection): void
    {
        $this->moduleTextCorrection = $moduleTextCorrection;
    }

    public function getObjectTextCreation(): ?string
    {
        return $this->objectTextCreation;
    }

    public function setObjectTextCreation(?string $objectTextCreation): void
    {
        $this->objectTextCreation = $objectTextCreation;
    }

    public function getObjectTextOptimization(): ?string
    {
        return $this->objectTextOptimization;
    }

    public function setObjectTextOptimization(?string $objectTextOptimization): void
    {
        $this->objectTextOptimization = $objectTextOptimization;
    }

    public function getObjectTextCorrection(): ?string
    {
        return $this->objectTextCorrection;
    }

    public function setObjectTextCorrection(?string $objectTextCorrection): void
    {
        $this->objectTextCorrection = $objectTextCorrection;
    }

    public function getData(): array
    {
        return [
            'id' => $this->getId(),
            'textProvider' => $this->getTextProvider(),
            'moduleTextCreation' => $this->getModuleTextCreation(),
            'moduleTextOptimization' => $this->getModuleTextOptimization(),
            'moduleTextCorrection' => $this->getModuleTextCorrection(),
            'objectTextCreation' => $this->getObjectTextCreation(),
            'objectTextOptimization' => $this->getObjectTextOptimization(),
            'objectTextCorrection' => $this->getObjectTextCorrection(),
        ];
    }
}
