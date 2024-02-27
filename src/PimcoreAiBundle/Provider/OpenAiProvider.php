<?php

namespace Instride\PimcoreAiBundle\Provider;

use OpenAI;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OpenAiProvider extends AbstractProvider
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

    public function getClient(): OpenAI\Client
    {
        $apiKey = $_ENV['OPEN_AI_API_KEY'];
//        $apiKey = $this->container->getParameter('pimcore_ai.open_ai.api_key');
        return OpenAI::client($apiKey);
    }

    public function getText(array $options): OpenAI\Responses\Chat\CreateResponse
    {
        if (!\array_key_exists('prompt', $options)) {
            throw new \RuntimeException('No text prompt given.');
        }

        $messages = $options['messages'] ?? null;

        $messages[] = [
            'role' => $options['role'] ?? 'user',
            'content' => $options['prompt']
        ];

        return $this->getClient()->chat()->create([
            'model' => $options['model'] ?? 'gpt-4',
            'messages' => $messages,
        ]);
    }

    public function getImage(array $options): OpenAI\Responses\Images\CreateResponse
    {
        if (!\array_key_exists('prompt', $options)) {
            throw new \RuntimeException('No image prompt given.');
        }

        return $this->getClient()->images()->create([
            'model' => $options['model'] ?? 'dall-e-3',
            'prompt' => $options['prompt'],
            'n' => $options[''] ?? 1,
            'size' => $options['size'] ?? '1024x1024',
            'response_format' => $options['response_format'] ?? 'url',
        ]);
    }
}
