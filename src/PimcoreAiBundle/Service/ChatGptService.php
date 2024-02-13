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

namespace Instride\Bundle\PimcoreAiBundle\Service;

use OpenAI;

class ChatGptService
{
    public function createChat(string $message): ?string
    {
        $apiKey = $_ENV['OPEN_AI_API_KEY'];
        $client = OpenAI::client($apiKey);

        $result = $client->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => $message],
            ],
        ]);

        return $result->choices[0]->message->content;
    }

    public function createImage(string $prompt)
    {
        $apiKey = $_ENV['OPEN_AI_API_KEY'];
        $client = OpenAI::client($apiKey);

        $response = $client->images()->create([
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'url',
        ]);

        return $response->toArray();
    }
}
