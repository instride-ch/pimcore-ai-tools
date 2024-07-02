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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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

    public function textCreationAction(Request $request): JsonResponse
    {
        $text = $request->get('text');
        $className = $request->get('class');
        $fieldName = $request->get('field');

        $configuration = $this->configurationService->getObjectConfiguration(
            $className,
            $fieldName,
            'text_creation'
        );

        $prompt = $configuration['prompt'] . $text;
        // ToDo: Get options from configuration and set it as third parameter (needs to be an array)
        $result = $this->promptService->getText($configuration['provider'], $prompt);

        return $this->json([
            'result' => $result,
            'id' => $request->get('id'),
        ]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function textCorrectionAction(Request $request): JsonResponse
    {
        $text = $request->get('text');
        $className = $request->get('class');
        $fieldName = $request->get('field');

        $configuration = $this->configurationService->getObjectConfiguration(
            $className,
            $fieldName,
            'text_correction'
        );

        $prompt = $configuration['prompt'] . $text;
        // ToDo: Get options from configuration and set it as third parameter (needs to be an array)
        $result = $this->promptService->getText($configuration['provider'], $prompt);

        return $this->json([
            'result' => $result,
            'id' => $request->get('id'),
        ]);
    }

    public function textOptimizationAction(Request $request): JsonResponse
    {
        $text = $request->get('text');
        $className = $request->get('class');
        $fieldName = $request->get('field');

        $configuration = $this->configurationService->getObjectConfiguration(
            $className,
            $fieldName,
            'text_optimization'
        );

        $prompt = $configuration['prompt'] . $text;
        // ToDo: Get options from configuration and set it as third parameter (needs to be an array)
        $result = $this->promptService->getText($configuration['provider'], $prompt);

        return $this->json([
            'result' => $result,
            'id' => $request->get('id'),
        ]);
    }
}
