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

namespace Instride\Bundle\PimcoreAiToolsBundle\Controller;

use Instride\Bundle\PimcoreAiToolsBundle\Services\ConfigurationService;
use Instride\Bundle\PimcoreAiToolsBundle\Services\PromptService;
use Pimcore\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PromptController extends Controller
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
        $result = $this->promptService->getText($configuration['provider'], $prompt, $configuration['options']);

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

        if ($type === 'frontend') {
            $name = $request->get('name');

            return $this->configurationService->getFrontendConfiguration(
                $name,
                $promptType,
            );
        }

        return [];
    }
}
