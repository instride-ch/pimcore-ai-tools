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

use Instride\Bundle\PimcoreAiToolsBundle\Model\AiEditableConfiguration\Dao;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\Exception\NotFoundException;

/**
 * @method Dao getDao()
 * @method void delete()
 * @method void save()
 */
final class AiEditableConfiguration extends AbstractModel
{
    private ?int $id = null;

    private ?string $areabrick = null;

    private ?string $editable = null;

    private ?string $type = null;

    private ?string $prompt = null;

    private ?string $options = null;

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

    public function getAreabrick(): ?string
    {
        return $this->areabrick;
    }

    public function setAreabrick(string $areabrick): void
    {
        $this->areabrick = $areabrick;
    }

    public function getEditable(): ?string
    {
        return $this->editable;
    }

    public function setEditable(string $editable): void
    {
        $this->editable = $editable;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getPrompt(): ?string
    {
        return $this->prompt;
    }

    public function setPrompt(string $prompt): void
    {
        $this->prompt = $prompt;
    }

    public function getOptions(): ?string
    {
        return $this->options;
    }

    public function setOptions(?string $options): void
    {
        $this->options = $options;
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
            'areabrick' => $this->getAreabrick(),
            'editable' => $this->getEditable(),
            'type' => $this->getType(),
            'prompt' => $this->getPrompt(),
            'options' => $this->getOptions(),
            'provider' => $this->getProvider(),
        ];
    }
}
