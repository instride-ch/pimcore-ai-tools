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

use Instride\Bundle\PimcoreAiBundle\Model\AiObjectConfiguration;
use Instride\Bundle\PimcoreAiBundle\Model\DataObject\ClassDefinition\Data\AiWysiwyg;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Model\DataObject\ClassDefinition\Listing;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ConfigurationController extends UserAwareController
{
    use JsonHelperTrait;

    public function syncEditablesAction(): JsonResponse
    {
        $this->checkPermission('pimcore_ai');

        return $this->jsonResponse(['success' => true]);
    }

    public function syncObjectsAction(): JsonResponse
    {
        $this->checkPermission('pimcore_ai');

        $classesList = new Listing();
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
                $this->createAiObjectConfiguration($className, $fieldName, 'text_create');
                $this->createAiObjectConfiguration($className, $fieldName, 'text_optimize');
                $this->createAiObjectConfiguration($className, $fieldName, 'text_correction');
            }
        }

        return $this->jsonResponse(['success' => true]);
    }

    private function createAiObjectConfiguration(string $className, string $fieldName, string $type): void
    {
        $objectConfiguration = new AiObjectConfiguration();
        $objectConfiguration->setClassName($className);
        $objectConfiguration->setFieldName($fieldName);
        $objectConfiguration->setType($type);
        $objectConfiguration->save();
    }
}
