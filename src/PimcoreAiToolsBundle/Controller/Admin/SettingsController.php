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

namespace Instride\Bundle\PimcoreAiToolsBundle\Controller\Admin;

use Instride\Bundle\PimcoreAiToolsBundle\Locator\ProviderLocator;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiDefaultsConfiguration;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiEditableConfiguration;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiFrontendConfiguration;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiObjectConfiguration;
use Instride\Bundle\PimcoreAiToolsBundle\Model\AiTranslationObjectConfiguration;
use Instride\Bundle\PimcoreAiToolsBundle\Model\DataObject\ClassDefinition\Data\AiWysiwyg;
use Pimcore\Bundle\AdminBundle\Helper\QueryParams;
use Pimcore\Cache;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Extension\Bundle\Exception\AdminClassicBundleNotFoundException;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @Route("/admin/pimcore-ai/settings")
 */
final class SettingsController extends UserAwareController
{
    use JsonHelperTrait;

    /**
     * @Route("/load-defaults", name="pimcore_ai_tools_settings_load_defaults", methods={"POST"})
     */
    public function loadDefaultsAction(): JsonResponse
    {
        $this->checkPermission('pimcore_ai');

        $defaultsConfiguration = AiDefaultsConfiguration::getById(1);
        if (!$defaultsConfiguration instanceof AiDefaultsConfiguration) {
            $defaultsConfiguration = new AiDefaultsConfiguration();
            $defaultsConfiguration->setTextProvider('OpenAi');
            $defaultsConfiguration->save();
        }

        return $this->jsonSuccess(['data' => $defaultsConfiguration->getData()]);
    }

    /**
     * @Route("/save-defaults", name="pimcore_ai_tools_settings_save_defaults", methods={"POST"})
     */
    public function saveDefaultsAction(Request $request): JsonResponse
    {
        $this->checkPermission('pimcore_ai');

        $data = $request->request->all();

        $defaultsConfiguration = AiDefaultsConfiguration::getById(1);
        if (!$defaultsConfiguration instanceof AiDefaultsConfiguration) {
            return $this->jsonError();
        }

        $defaultsConfiguration->setValues($data);
        $defaultsConfiguration->save();

        return $this->jsonSuccess();
    }

    /**
     * @Route("/sync-editables", name="pimcore_ai_tools_settings_sync_editables", methods={"POST"})
     * @throws \JsonException
     */
    public function syncEditablesAction(): JsonResponse
    {
        $editables = \json_decode($this->getParameter('pimcore_ai_tools.editables'), true, 512, JSON_THROW_ON_ERROR);

        // Get all AiEditableConfiguration from database
        $list = new AiEditableConfiguration\Listing();
        $list->load();

        // Check if config already exists
        $configsToCreate = $editables;
        foreach ($list->getEditableConfigurations() as $editableConfiguration) {
            $configuration = $editableConfiguration->getData();
            $areabrick = $configuration['areabrick'];
            $editableId = $configuration['editable'];

            // Ai field and config exists: Unset value in aiFields array
            if (\array_key_exists($areabrick, $editables) &&
                ($key = \array_search($editableId, $editables[$areabrick], true)) !== false) {
                unset($configsToCreate[$areabrick][$key]);

                continue;
            }

            // Config should not exist: delete
            $editableConfiguration->delete();
        }

        // Create new configs
        foreach ($configsToCreate as $areabrick => $editables) {
            foreach ($editables as $editableId) {
                $this->createAiEditableConfiguration($areabrick, $editableId, 'text_creation');
                $this->createAiEditableConfiguration($areabrick, $editableId, 'text_optimization');
                $this->createAiEditableConfiguration($areabrick, $editableId, 'text_correction');
                $this->createAiEditableConfiguration($areabrick, $editableId, 'text_refinement');
            }
        }

        return $this->jsonSuccess();
    }

    /**
     * @Route("/sync-objects", name="pimcore_ai_tools_settings_sync_objects", methods={"POST"})
     */
    public function syncObjectsAction(): JsonResponse
    {
        $classesList = new ClassDefinition\Listing();
        $classesList->setOrderKey('name');
        $classesList->setOrder('asc');
        $classes = $classesList->load();

        // Get all AiWysiwyg fields from class definitions
        $aiFields = [];
        foreach ($classes as $class) {
            $fields = $class->getFieldDefinitions();

            foreach ($fields as $field) {
                if ($field instanceof AiWysiwyg) {
                    $aiFields[$class->getName()][] = $field->getName();
                }
            }
        }

        // Get all AiObjectConfigurations from database
        $list = new AiObjectConfiguration\Listing();
        $list->load();

        // Check if config already exists
        $configsToCreate = $aiFields;
        foreach ($list->getObjectConfigurations() as $objectConfiguration) {
            $configuration = $objectConfiguration->getData();
            $className = $configuration['className'];
            $fieldName = $configuration['fieldName'];

            // Ai field and config exists: Unset value in aiFields array
            if (\array_key_exists($className, $aiFields) &&
                ($key = \array_search($fieldName, $aiFields[$className], true)) !== false) {
                unset($configsToCreate[$className][$key]);

                continue;
            }

            // Config should not exist: delete
            $objectConfiguration->delete();
        }

        // Create new configs
        foreach ($configsToCreate as $className => $fields) {
            foreach ($fields as $fieldName) {
                $this->createAiObjectConfiguration($className, $fieldName, 'text_creation');
                $this->createAiObjectConfiguration($className, $fieldName, 'text_optimization');
                $this->createAiObjectConfiguration($className, $fieldName, 'text_correction');
                $this->createAiObjectConfiguration($className, $fieldName, 'text_refinement');
            }
        }

        return $this->jsonSuccess();
    }

    /**
     * @Route("/sync-frontend", name="pimcore_ai_tools_settings_sync_frontend", methods={"POST"})
     */
    public function syncFrontendAction(): JsonResponse
    {
        $frontend = \json_decode($this->getParameter('pimcore_ai_tools.frontend'), true, 512, JSON_THROW_ON_ERROR);

        // Get all AiFrontendConfiguration from database
        $list = new AiFrontendConfiguration\Listing();
        $list->load();

        $existingConfigs = [];
        foreach ($list->getFrontendConfigurations() as $frontendConfiguration) {
            $data = $frontendConfiguration->getData();
            $existingConfigs[] = [
                'name' => $data['name'],
                'type' => $data['type'],
            ];
        }

        $types = ['text_creation', 'text_optimization', 'text_correction', 'text_refinement'];
        $configsToCreate = [];

        foreach ($frontend as $name) {
            foreach ($types as $type) {
                $configExists = array_filter($existingConfigs, function ($config) use ($name, $type) {
                    return $config['name'] === $name && $config['type'] === $type;
                });

                if (empty($configExists)) {
                    $configsToCreate[] = ['name' => $name, 'type' => $type];
                }
            }
        }

        foreach ($configsToCreate as $config) {
            $this->createAiFrontendConfiguration($config['name'], $config['type']);
        }

        return $this->jsonSuccess();
    }

    /**
     * @Route("/editable-configuration", name="pimcore_ai_tools_settings_editable_configuration", methods={"POST"})
     */
    public function editableConfigurationAction(Request $request): JsonResponse
    {
        if ($request->get('data')) {
            Cache::clearTag('pimcore_ai_editable');

            $action = $request->get('xaction');
            $data = $this->decodeJson($request->get('data'));
            $editableConfiguration = AiEditableConfiguration::getById($data['id']);

            if ($editableConfiguration && $action === 'update') {
                $editableConfiguration->setValues($data);
                $editableConfiguration->save();

                return $this->jsonResponse(['data' => $editableConfiguration, 'success' => true]);
            }
        } else {
            if (!\class_exists(QueryParams::class)) {
                throw new AdminClassicBundleNotFoundException('This action requires package "pimcore/admin-ui-classic-bundle" to be installed.');
            }

            $list = new AiEditableConfiguration\Listing();
            $list->setLimit((int) $request->get('limit', 50));
            $list->setOffset((int) $request->get('start', 0));

            $sortingSettings = QueryParams::extractSortingSettings(\array_merge($request->request->all(), $request->query->all()));
            if ($sortingSettings['orderKey']) {
                $list->setOrderKey($sortingSettings['orderKey']);
                $list->setOrder($sortingSettings['order']);
            }

            if ($request->get('filter')) {
                $list->setCondition('`areabrick` LIKE ' . $list->quote('%'.$request->get('filter').'%'));
            }

            $list->load();

            $editableConfigurations = [];
            foreach ($list->getEditableConfigurations() as $editableConfiguration) {
                $editableConfigurations[] = $editableConfiguration->getData();
            }

            return $this->jsonSuccess([
                'data' => $editableConfigurations,
                'total' => $list->getTotalCount(),
            ]);
        }

        return $this->jsonError();
    }

    /**
     * @Route("/delete-translation-object", name="pimcore_ai_tools_settings_delete_translation_object", methods={"POST"})
     */
    public function deleteTranslationObjectAction(Request $request): JsonResponse
    {
        $this->checkPermission('pimcore_ai');

        $data = json_decode($request->getContent(), true);
        if (!isset($data['id']) || !is_numeric($data['id'])) {
            return $this->jsonError('No valid ID provided.');
        }

        $translationObject = AiTranslationObjectConfiguration::getById((int)$data['id']);
        if (!$translationObject instanceof AiTranslationObjectConfiguration) {
            return $this->jsonError('Configuration not found.');
        }

        try {
            $translationObject->delete();
            return $this->jsonSuccess();
        } catch (\Exception $error) {
            return $this->jsonError('Delete failed: ' . $error->getMessage());
        }
    }

    /**
     * @Route("/save-translation-object", name="pimcore_ai_tools_settings_save_translation_object", methods={"POST"})
     */
    public function saveTranslationObjectAction(Request $request): JsonResponse
    {
        $this->checkPermission('pimcore_ai');
        $data = json_decode($request->getContent(), true);

        if (!$data || empty($data['className']) || empty($data['field'])) {
            return $this->jsonError('Data is incomplete');
        }

        $fields = is_array($data['field']) ? implode(',', array_map('trim', $data['field'])) : '';

        if (AiTranslationObjectConfiguration::hasExistingClass($data['className'])) {
            return $this->jsonError('This class is already configured');
        }

        $translationObjectConfiguration = new AiTranslationObjectConfiguration();
        $translationObjectConfiguration->setClassName($data['className']);
        $translationObjectConfiguration->setFields($fields);
        $translationObjectConfiguration->setStandardLanguage($data['standardLanguage'] ?? '');
        $translationObjectConfiguration->setProvider($data['provider'] ?? '');

        try {
            $translationObjectConfiguration->save();
            return $this->jsonSuccess();
        } catch (\Exception $error) {
            return $this->jsonError($error->getMessage());
        }
    }

    public function translationObjectConfigurationAction(Request $request): JsonResponse
    {
        if ($request->get('data')) {
            Cache::clearTag('pimcore_ai_translation_object');

            $action = $request->get('xaction');
            $data = $this->decodeJson($request->get('data'));
            if (!isset($data['id']) || !is_numeric($data['id'])) {
                $translationObjectConfiguration = new AiTranslationObjectConfiguration();
            } else {
                $translationObjectConfiguration = AiTranslationObjectConfiguration::getById($data['id']);
            }

            if ($translationObjectConfiguration && $action === 'update') {
                if (!empty($data['fields']) && is_array($data['fields'])) {
                    $data['fields'] = implode(',', array_map('trim', $data['fields']));
                }

                $translationObjectConfiguration->setValues($data);
                $translationObjectConfiguration->save();

                return $this->jsonResponse(['data' => $translationObjectConfiguration, 'success' => true]);
            }
        } else {
            if (!\class_exists(QueryParams::class)) {
                throw new AdminClassicBundleNotFoundException('This action requires package "pimcore/admin-ui-classic-bundle" to be installed.');
            }

            $list = new AiTranslationObjectConfiguration\Listing();
            $list->setLimit((int) $request->get('limit', 50));
            $list->setOffset((int) $request->get('start', 0));

            $sortingSettings = QueryParams::extractSortingSettings(\array_merge($request->request->all(), $request->query->all()));
            if ($sortingSettings['orderKey']) {
                $list->setOrderKey($sortingSettings['orderKey']);
                $list->setOrder($sortingSettings['order']);
            }

            if ($request->get('filter')) {
                $list->setCondition('`name` LIKE ' . $list->quote('%'.$request->get('filter').'%'));
            }

            $list->load();

            $translationObjectConfigurations = [];
            foreach ($list->getTranslationObjectConfigurations() as $translationObjectConfiguration) {
                $translationObjectConfigurations[] = $translationObjectConfiguration->getData();
            }

            return $this->jsonSuccess([
                'data' => $translationObjectConfigurations,
                'total' => $list->getTotalCount(),
            ]);
        }

        return $this->jsonError();
    }

    /**
     * @Route("/object-configuration", name="pimcore_ai_tools_settings_object_configuration", methods={"POST"})
     */
    public function objectConfigurationAction(Request $request): JsonResponse
    {
        if ($request->get('data')) {
            Cache::clearTag('pimcore_ai_objects');

            $action = $request->get('xaction');
            $data = $this->decodeJson($request->get('data'));
            $objectConfiguration = AiObjectConfiguration::getById($data['id']);

            if ($objectConfiguration && $action === 'update') {
                $objectConfiguration->setValues($data);
                $objectConfiguration->save();

                return $this->jsonSuccess(['data' => $objectConfiguration]);
            }
        } else {
            if (!\class_exists(QueryParams::class)) {
                throw new AdminClassicBundleNotFoundException('This action requires package "pimcore/admin-ui-classic-bundle" to be installed.');
            }

            $list = new AiObjectConfiguration\Listing();
            $list->setLimit((int) $request->get('limit', 50));
            $list->setOffset((int) $request->get('start', 0));

            $sortingSettings = QueryParams::extractSortingSettings(\array_merge($request->request->all(), $request->query->all()));
            if ($sortingSettings['orderKey']) {
                $list->setOrderKey($sortingSettings['orderKey']);
                $list->setOrder($sortingSettings['order']);
            }

            if ($request->get('filter')) {
                $list->setCondition('`className` LIKE ' . $list->quote('%'.$request->get('filter').'%'));
            }

            $list->load();

            $objectConfigurations = [];
            foreach ($list->getObjectConfigurations() as $objectConfiguration) {
                $objectConfigurations[] = $objectConfiguration->getData();
            }

            return $this->jsonSuccess([
                'data' => $objectConfigurations,
                'total' => $list->getTotalCount(),
            ]);
        }

        return $this->jsonError();
    }

    /**
     * @Route("/frontend-configuration", name="pimcore_ai_tools_settings_frontend_configuration", methods={"POST"})
     */
    public function frontendConfigurationAction(Request $request): JsonResponse
    {
        if ($request->get('data')) {
            Cache::clearTag('pimcore_ai_frontend');

            $action = $request->get('xaction');
            $data = $this->decodeJson($request->get('data'));
            $frontendConfiguration = AiFrontendConfiguration::getById($data['id']);

            if ($frontendConfiguration && $action === 'update') {
                $frontendConfiguration->setValues($data);
                $frontendConfiguration->save();

                return $this->jsonResponse(['data' => $frontendConfiguration, 'success' => true]);
            }
        } else {
            if (!\class_exists(QueryParams::class)) {
                throw new AdminClassicBundleNotFoundException('This action requires package "pimcore/admin-ui-classic-bundle" to be installed.');
            }

            $list = new AiFrontendConfiguration\Listing();
            $list->setLimit((int) $request->get('limit', 50));
            $list->setOffset((int) $request->get('start', 0));

            $sortingSettings = QueryParams::extractSortingSettings(\array_merge($request->request->all(), $request->query->all()));
            if ($sortingSettings['orderKey']) {
                $list->setOrderKey($sortingSettings['orderKey']);
                $list->setOrder($sortingSettings['order']);
            }

            if ($request->get('filter')) {
                $list->setCondition('`name` LIKE ' . $list->quote('%'.$request->get('filter').'%'));
            }

            $list->load();

            $frontendConfigurations = [];
            foreach ($list->getFrontendConfigurations() as $frontendConfiguration) {
                $frontendConfigurations[] = $frontendConfiguration->getData();
            }

            return $this->jsonSuccess([
                'data' => $frontendConfigurations,
                'total' => $list->getTotalCount(),
            ]);
        }

        return $this->jsonError();
    }

    /**
     * @Route("/get-text-providers", name="pimcore_ai_tools_settings_get_text_providers", methods={"GET"})
     */
    public function getTextProvidersAction(ProviderLocator $providerLocator): JsonResponse
    {
        $textProviders = $providerLocator->getTextProviders();

        $data = [];
        foreach ($textProviders as $textProvider) {
            $data[] = [
                'value' => $textProvider,
                'name' => $textProvider,
            ];
        }

        return $this->jsonSuccess($data);
    }

    /**
     * @Route("/get-image-providers", name="pimcore_ai_tools_settings_get_image_providers", methods={"GET"})
     */
    public function getImageProvidersAction(ProviderLocator $providerLocator): JsonResponse
    {
        $imageProviders = $providerLocator->getImageProviders();

        $data = [];
        foreach ($imageProviders as $imageProvider) {
            $data[] = [
                'value' => $imageProvider,
                'name' => $imageProvider,
            ];
        }

        return $this->jsonSuccess($data);
    }

    private function createAiEditableConfiguration(string $areabrick, string $editable, string $type): void
    {
        $objectConfiguration = new AiEditableConfiguration();
        $objectConfiguration->setAreabrick($areabrick);
        $objectConfiguration->setEditable($editable);
        $objectConfiguration->setType($type);
        $objectConfiguration->save();
    }

    private function createAiObjectConfiguration(string $className, string $fieldName, string $type): void
    {
        $objectConfiguration = new AiObjectConfiguration();
        $objectConfiguration->setClassName($className);
        $objectConfiguration->setFieldName($fieldName);
        $objectConfiguration->setType($type);
        $objectConfiguration->save();
    }

    private function createAiFrontendConfiguration(string $name, string $type): void
    {
        $frontendConfiguration = new AiFrontendConfiguration();
        $frontendConfiguration->setName($name);
        $frontendConfiguration->setType($type);
        $frontendConfiguration->save();
    }

    private function jsonSuccess(array $data = []): JsonResponse
    {
        return $this->jsonResponse(array_merge(['success' => true], $data));
    }

    private function jsonError(string $message = "An error occurred."): JsonResponse
    {
        return $this->jsonResponse(['success' => false, 'error' => $message]);
    }
}
