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

namespace Instride\Bundle\PimcoreAiToolsBundle\Services;

use Instride\Bundle\PimcoreAiToolsBundle\Provider\TextProviderInterface;

final class PromptService
{
    public function getText(
        TextProviderInterface $textProvider,
        string $prompt,
        ?array $options = null,
    ): string
    {
        $options['prompt'] = $prompt;

        return $this->refineText($textProvider->getText($options));
    }

    private function refineText(string $text): string
    {
        return trim($text, "\"");
    }
}
