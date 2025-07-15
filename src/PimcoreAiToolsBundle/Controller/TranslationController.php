<?php

namespace Instride\Bundle\PimcoreAiToolsBundle\Controller;

use Instride\Bundle\PimcoreAiToolsBundle\Provider\TextProviderInterface;
use Instride\Bundle\PimcoreAiToolsBundle\Services\ConfigurationService;
use Instride\Bundle\PimcoreAiToolsBundle\Services\PromptService;
use Pimcore\Controller\Controller;
use Pimcore\Model\DataObject;
use Pimcore\Tool;
use Symfony\Component\HttpFoundation\JsonResponse;
class TranslationController extends Controller
{
    private ConfigurationService $configurationService;
    private PromptService $promptService;

    public function __construct(ConfigurationService $configurationService, PromptService $promptService)
    {
        $this->configurationService = $configurationService;
        $this->promptService = $promptService;
    }

    public function translateAction(int $objectId, string $className, string $toLanguage): JsonResponse
    {
        $configuration = $this->configurationService->getTranslationObjectConfiguration($className);
        if (!$configuration) {
            return $this->jsonErrorResponse('No translation configuration found');
        }

        $defaultLanguage = $configuration['standardLanguage'];
        if ($defaultLanguage === $toLanguage) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Ignore generation for default language',
            ]);
        }

        $object = DataObject::getById($objectId);
        if (!$object) {
            return $this->jsonErrorResponse('Object not found');
        }

        $translations = [];
        $errors = [];
        $fieldNames = $configuration['fields'];
        $fieldNamesArray = \explode(',', $fieldNames);

        foreach ($fieldNamesArray as $fieldName) {
            if (!$this->canTranslateField($object, $fieldName, $toLanguage, $defaultLanguage)) {
                continue;
            }

            try {
                $translatedContent = $this->translateField($object, $fieldName, $defaultLanguage, $toLanguage, $configuration['provider']);
                if ($translatedContent !== null) {
                    $setter = 'set' . \ucfirst($fieldName);
                    $object->$setter($translatedContent, $toLanguage);
                    $translations[$fieldName] = $translatedContent;
                }
            } catch (\Exception $e) {
                $errors[] = "Translation failed for '$fieldName': " . $e->getMessage();
            }
        }

        if (!empty($translations)) {
            $object->save();
        }

        return $this->jsonResponse($translations, $errors);
    }

    public function translateAllAction(int $objectId, string $className): JsonResponse
    {
        $validLanguages = Tool::getValidLanguages();

        $response = null;
        foreach ($validLanguages as $language) {
            $responsePerLanguage = $this->translateAction($objectId, $className, $language);
            $response[$language] = $responsePerLanguage->getContent();
        }

        return new JsonResponse($response);
    }

    private function canTranslateField($object, string $fieldName, string $toLanguage, string $defaultLanguage): bool
    {
        $getter = 'get' . ucfirst($fieldName);
        $setter = 'set' . ucfirst($fieldName);

        if (!method_exists($object, $getter) || !method_exists($object, $setter)) {
            return false;
        }

        $existingTranslation = $object->$getter($toLanguage);
        if (!empty($existingTranslation)) {
            return false;
        }

        $content = $object->$getter($defaultLanguage);
        return (!empty($content));
    }


    private function translateField($object, string $fieldName, string $defaultLanguage, string $toLanguage, TextProviderInterface $provider): ?string
    {
        $getter = 'get' . ucfirst($fieldName);
        $content = $object->$getter($defaultLanguage);

        $prompt = "Translate the following text to $toLanguage. If the translation is the same as the original text, please type the same text.";
        return $this->promptService->getText($provider, $prompt . "\n\n" . $content);
    }

    private function jsonResponse(array $translations, array $errors): JsonResponse
    {
        return new JsonResponse([
            'success' => empty($errors),
            'message' => empty($errors) ? 'Translation completed' : 'Translation completed with errors',
            'translations' => $translations,
            'errors' => $errors
        ]);
    }

    private function jsonErrorResponse(string $message): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
            'translations' => [],
            'errors' => []
        ]);
    }
}
