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

namespace Instride\Bundle\PimcoreAiBundle\Controller\Admin;

use Instride\Bundle\PimcoreAiBundle\Services\PromptService;
use Instride\Bundle\PimcoreAiBundle\Services\ConfigurationService;
use Pimcore\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class PromptController extends Controller
{
    private PromptService $promptService;
    private ConfigurationService $configurationService;

    public function __construct(PromptService $promptService, ConfigurationService $configurationService)
    {
        $this->promptService = $promptService;
        $this->configurationService = $configurationService;
    }

    public function textAction(Request $request): JsonResponse
    {
        $id = $request->get('id');
        $text = $request->get('text');
        $type = $request->get('type');
        $promptType = $request->get('promptType');

        $configuration = $this->getConfiguration($type, $promptType, $request);

        $prompt = $configuration['prompt'] . $text;
        // ToDo: Get options from configuration and set it as third parameter (needs to be an array)
        $result = $this->promptService->getText($configuration['provider'], $prompt);

        return $this->json([
            'result' => $result,
            'id' => $id,
        ]);
    }

    private function getConfiguration(string $type, string $promptType, Request $request): array
    {
        if ($type === 'object') {
            $className = $request->get('class');
            $fieldName = $request->get('field');

            return $this->configurationService->getObjectConfiguration(
                $className,
                $fieldName,
                $promptType,
            );
        }

        if ($type === 'editable') {
            $areabrick = $request->get('areabrick');
            $editable = $request->get('editable');

            return $this->configurationService->getEditableConfiguration(
                $areabrick,
                $editable,
                $promptType,
            );
        }

        return [];
    }
}
