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

namespace Instride\Bundle\PimcoreAiToolsBundle\Provider;

use Exception;
use Ferranfg\MidjourneyPhp\Midjourney;

class MidjourneyProvider extends AbstractProvider implements ImageProviderInterface
{
    private int $channelId;
    private string $authKey;

    public function __construct(int $channelId, string $authKey)
    {
        $this->channelId = $channelId;
        $this->authKey = $authKey;
    }

    public static function getName(): string
    {
        return 'Midjourney';
    }

    public function getClient(): Midjourney
    {
        return new Midjourney($this->channelId, $this->authKey);
    }

    public function getImage(array $options): ?object
    {
        if (!\array_key_exists('prompt', $options)) {
            throw new \RuntimeException('No image prompt given.');
        }

        $prompt = $this->getPromptString($options);

        $existingImage = $this->getClient()->getImagine($prompt);
        if ($existingImage) {
            return $existingImage;
        }

        return $this->getClient()->imagine($prompt);
    }

    /**
     * @throws Exception
     */
    public function getUpscaleImage(array $options, int $imageIndex = 0): mixed
    {
        if (!\array_key_exists('prompt', $options)) {
            throw new \RuntimeException('No image prompt given.');
        }

        $prompt = $this->getPromptString($options);
        $imagineObject = $this->getClient()->getImagine($prompt);

        $existingImage = $this->getClient()->getUpscale($imagineObject, $imageIndex);
        if ($existingImage) {
            return $existingImage;
        }

        return $this->getClient()->upscale($imagineObject, $imageIndex);
    }

    private function getPromptString(array $options): string
    {
        $seed = $options['seed'] ?? null;

        $prompt = $options['prompt'];
        if ($seed) {
            $prompt .= ' --seed '. $seed;
        }

        return $prompt;
    }
}
