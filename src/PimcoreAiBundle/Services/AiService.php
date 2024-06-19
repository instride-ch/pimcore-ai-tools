<?php

declare(strict_types=1);

/**
 * instride AG.
 *
 * LICENSE
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that is distributed with this source code.
 *
 * @copyright  Copyright (c) 2024 instride AG (https://instride.ch)
 */

namespace Instride\Bundle\PimcoreAiBundle\Services;

use Instride\Bundle\PimcoreAiBundle\Provider\TextProviderInterface;

final class AiService
{
    private TextProviderInterface $defaultTextProvider;

    public function __construct(TextProviderInterface $defaultTextProvider)
    {
        $this->defaultTextProvider = $defaultTextProvider;
    }

    public function getText(
        string $prompt,
        array $options = [],
        TextProviderInterface $customTextProvider = null
    ): string
    {
        $textProvider = $this->defaultTextProvider;

        if ($customTextProvider && $customTextProvider !== $this->defaultTextProvider) {
            $textProvider = $customTextProvider;
        }

        $options['prompt'] = $prompt;

        $response = $textProvider->getText($options);

        // ToDo: Error handling
        return $response['choices'][0]['message']['content'];
    }
}
