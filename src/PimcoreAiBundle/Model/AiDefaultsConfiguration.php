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

    private ?string $textModuleCreate = null;

    private ?string $textModuleOptimize = null;

    private ?string $textModuleCorrect = null;

    private ?string $textObjectCreate = null;

    private ?string $textObjectOptimize = null;

    private ?string $textObjectCorrect = null;


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

    public function getTextModuleCreate(): ?string
    {
        return $this->textModuleCreate;
    }

    public function setTextModuleCreate(?string $textModuleCreate): void
    {
        $this->textModuleCreate = $textModuleCreate;
    }

    public function getTextModuleOptimize(): ?string
    {
        return $this->textModuleOptimize;
    }

    public function setTextModuleOptimize(?string $textModuleOptimize): void
    {
        $this->textModuleOptimize = $textModuleOptimize;
    }

    public function getTextModuleCorrect(): ?string
    {
        return $this->textModuleCorrect;
    }

    public function setTextModuleCorrect(?string $textModuleCorrect): void
    {
        $this->textModuleCorrect = $textModuleCorrect;
    }

    public function getTextObjectCreate(): ?string
    {
        return $this->textObjectCreate;
    }

    public function setTextObjectCreate(?string $textObjectCreate): void
    {
        $this->textObjectCreate = $textObjectCreate;
    }

    public function getTextObjectOptimize(): ?string
    {
        return $this->textObjectOptimize;
    }

    public function setTextObjectOptimize(?string $textObjectOptimize): void
    {
        $this->textObjectOptimize = $textObjectOptimize;
    }

    public function getTextObjectCorrect(): ?string
    {
        return $this->textObjectCorrect;
    }

    public function setTextObjectCorrect(?string $textObjectCorrect): void
    {
        $this->textObjectCorrect = $textObjectCorrect;
    }

    public function getData(): array
    {
        return [
            'id' => $this->getId(),
            'textProvider' => $this->getTextProvider(),
            'textModuleCreate' => $this->getTextModuleCreate(),
            'textModuleOptimize' => $this->getTextModuleOptimize(),
            'textModuleCorrect' => $this->getTextModuleCorrect(),
            'textObjectCreate' => $this->getTextObjectCreate(),
            'textObjectOptimize' => $this->getTextObjectOptimize(),
            'textObjectCorrect' => $this->getTextObjectCorrect(),
        ];
    }
}
