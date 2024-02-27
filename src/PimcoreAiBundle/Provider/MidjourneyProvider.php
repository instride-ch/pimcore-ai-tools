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

namespace Instride\PimcoreAiBundle\Provider;

use Exception;
use Ferranfg\MidjourneyPhp\Midjourney;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MidjourneyProvider extends AbstractProvider
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getClient(): Midjourney
    {
//        $discordChannelId = $this->container->getParameter('pimcore_ai.midjourney.channel_id');
//        $discordUserToken = $this->container->getParameter('pimcore_ai.midjourney.auth_key');
        $discordChannelId = (int)$_ENV['MIDJOURNEY_CHANNEL_ID'];
        $discordUserToken = $_ENV['MIDJOURNEY_AUTH_TOKEN'];

        return new Midjourney($discordChannelId, $discordUserToken);
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

    public function getText(array $options): mixed
    {
        throw new \RuntimeException('No text generation for midjourney available.');
    }

    private function getPromptString(array $options): string
    {
//        $seed = $options['seed'] ?? null;

        $prompt = $options['prompt'];
//        if ($seed) {
//            $prompt .= ' --seed '. $seed;
//        }

        return $prompt;
    }
}
