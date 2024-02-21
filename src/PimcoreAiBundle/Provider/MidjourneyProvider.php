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

use eDiasoft\Midjourney\Exceptions\MidjourneyException;
use eDiasoft\Midjourney\MidjourneyApiClient;
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

    public function getClient(): MidjourneyApiClient
    {
        $channel_id = $this->container->getParameter('pimcore_ai.midjourney.channel_id');
        $authorization = $this->container->getParameter('pimcore_ai.midjourney.auth_key');

        return new MidjourneyApiClient($channel_id, $authorization);
    }

    public function getImage(array $options): mixed
    {
        if (!array_key_exists('prompt', $options)) {
            throw new \RuntimeException('No image prompt given.');
        }

        return $this->getClient()->imagine($options['prompt'])->send();
    }

    /**
     * @throws MidjourneyException
     */
    public function getUpscaleImage(mixed $imageResult): mixed
    {
        $messageId = "1234";
        $upscaleImageId = "MJ::JOB::upsample::1::xxxxx";
        $interactionId = $imageResult->interactionId(); //You can retrieve this ID after the imagine interaction is performed, this is a identifier for the specific job request.

        $upscale_builder = $this->getClient()->upscale($messageId, $upscaleImageId, $interactionId);

        return $upscale_builder->send();
    }

    public function getText(array $options): mixed
    {
        throw new \RuntimeException('No text generation for midjourney available.');
    }
}
