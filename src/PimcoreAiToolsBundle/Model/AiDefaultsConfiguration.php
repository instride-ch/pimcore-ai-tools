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

use Instride\Bundle\PimcoreAiToolsBundle\Model\AiDefaultsConfiguration\Dao;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\Exception\NotFoundException;

/**
 * @method Dao getDao()
 * @method void delete()
 * @method void save()
 */
final class AiDefaultsConfiguration extends AbstractModel
{
    private ?int $id = null;

    private ?string $textProvider = null;

    private ?string $editableTextCreation = null;

    private ?string $editableTextOptimization = null;

    private ?string $editableTextCorrection = null;

    private ?string $objectTextCreation = null;

    private ?string $objectTextOptimization = null;

    private ?string $objectTextCorrection = null;

    private ?string $frontendTextCreation = null;

    private ?string $frontendTextOptimization = null;

    private ?string $frontendTextCorrection = null;


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

    public function getEditableTextCreation(): ?string
    {
        return $this->editableTextCreation;
    }

    public function setEditableTextCreation(?string $editableTextCreation): void
    {
        $this->editableTextCreation = $editableTextCreation;
    }

    public function getEditableTextOptimization(): ?string
    {
        return $this->editableTextOptimization;
    }

    public function setEditableTextOptimization(?string $editableTextOptimization): void
    {
        $this->editableTextOptimization = $editableTextOptimization;
    }

    public function getEditableTextCorrection(): ?string
    {
        return $this->editableTextCorrection;
    }

    public function setEditableTextCorrection(?string $editableTextCorrection): void
    {
        $this->editableTextCorrection = $editableTextCorrection;
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

    public function getFrontendTextCreation(): ?string
    {
        return $this->frontendTextCreation;
    }

    public function setFrontendTextCreation(?string $frontendTextCreation): void
    {
        $this->frontendTextCreation = $frontendTextCreation;
    }

    public function getFrontendTextOptimization(): ?string
    {
        return $this->frontendTextOptimization;
    }

    public function setFrontendTextOptimization(?string $frontendTextOptimization): void
    {
        $this->frontendTextOptimization = $frontendTextOptimization;
    }

    public function getFrontendTextCorrection(): ?string
    {
        return $this->frontendTextCorrection;
    }

    public function setFrontendTextCorrection(?string $frontendTextCorrection): void
    {
        $this->frontendTextCorrection = $frontendTextCorrection;
    }

    public function getData(): array
    {
        return [
            'id' => $this->getId(),
            'textProvider' => $this->getTextProvider(),
            'editableTextCreation' => $this->getEditableTextCreation(),
            'editableTextOptimization' => $this->getEditableTextOptimization(),
            'editableTextCorrection' => $this->getEditableTextCorrection(),
            'objectTextCreation' => $this->getObjectTextCreation(),
            'objectTextOptimization' => $this->getObjectTextOptimization(),
            'objectTextCorrection' => $this->getObjectTextCorrection(),
            'frontendTextCreation' => $this->getFrontendTextCreation(),
            'frontendTextOptimization' => $this->getFrontendTextOptimization(),
            'frontendTextCorrection' => $this->getFrontendTextCorrection(),
        ];
    }
}
