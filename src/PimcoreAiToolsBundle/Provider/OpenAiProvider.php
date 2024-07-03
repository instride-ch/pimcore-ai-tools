<?php

namespace Instride\Bundle\PimcoreAiToolsBundle\Provider;

use OpenAI;

class OpenAiProvider extends AbstractProvider implements TextProviderInterface, ImageProviderInterface
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public static function getName(): string
    {
        return 'OpenAi';
    }

    public function getClient(): OpenAI\Client
    {
        return OpenAI::client($this->apiKey);
    }

    public function getText(array $options): string
    {
        if (!\array_key_exists('prompt', $options)) {
            throw new \RuntimeException('No text prompt given.');
        }

        $messages = $options['messages'] ?? null;

        $messages[] = [
            'role' => $options['role'] ?? 'user',
            'content' => $options['prompt']
        ];

        $response = $this->getClient()->chat()->create([
            'model' => $options['model'] ?? 'gpt-4',
            'messages' => $messages,
        ]);

        return $response['choices'][0]['message']['content'];
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
