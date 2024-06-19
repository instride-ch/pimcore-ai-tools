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

use Instride\Bundle\PimcoreAiBundle\Services\AiService;
use Pimcore\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class PromptController extends Controller
{
    private AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function textCreationAction(Request $request): JsonResponse
    {
        return $this->json([
            'success' => true
        ]);
    }

    public function textCorrectionAction(Request $request): JsonResponse
    {
        $text = $request->get('text');

        // ToDo: Get prompt and options from config
        $prompt = "Bitte korrigere folgenden Text und behalte alle html tags bei: " . $text;
        $result = $this->aiService->getText($prompt);

        return $this->json([
            'result' => $result
        ]);
    }

    public function textOptimizationAction(Request $request): JsonResponse
    {
        return $this->json([
            'success' => true,
            'test' => 'hallo'
        ]);
    }
}
