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

use Instride\Bundle\PimcoreAiBundle\Model\AiEditableConfiguration;
use Instride\Bundle\PimcoreAiBundle\Model\AiObjectConfiguration;
use Pimcore\Bundle\AdminBundle\Helper\QueryParams;
use Pimcore\Cache;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Extension\Bundle\Exception\AdminClassicBundleNotFoundException;
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
     * @Route("/editable-configuration", name="pimcore_ai_settings_editable_configuration", methods={"POST"})
     *
     *
     */
    public function editableConfigurationAction(Request $request): JsonResponse
    {
        $this->checkPermission('pimcore_ai');

        if ($request->get('data')) {
            Cache::clearTag('pimcore_ai_editable');

            $action = $request->get('xaction');
            $data = $this->decodeJson($request->get('data'));
            $editableConfiguration = AiEditableConfiguration::getById($data['id']);

            if ($editableConfiguration && $action === 'destroy') {
                $editableConfiguration->delete();

                return $this->jsonResponse(['success' => true, 'data' => []]);
            }

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
                $list->setCondition('`editableId` LIKE ' . $list->quote('%'.$request->get('filter').'%'));
            }

            $list->load();

            $editableConfigurations = [];
            foreach ($list->getEditableConfigurations() as $editableConfiguration) {
                $editableConfigurations[] = $editableConfiguration->getData();
            }

            return $this->jsonResponse(['data' => $editableConfigurations, 'success' => true, 'total' => $list->getTotalCount()]);
        }

        return $this->jsonResponse(['success' => false]);
    }

    /**
     * @Route("/object-configuration", name="pimcore_ai_settings_object_configuration", methods={"POST"})
     *
     *
     */
    public function objectConfigurationAction(Request $request): JsonResponse
    {
        $this->checkPermission('pimcore_ai');

        if ($request->get('data')) {
            Cache::clearTag('pimcore_ai_objects');

            $action = $request->get('xaction');
            $data = $this->decodeJson($request->get('data'));
            $objectConfiguration = AiObjectConfiguration::getById($data['id']);

            if ($objectConfiguration && $action === 'destroy') {
                $objectConfiguration->delete();

                return $this->jsonResponse(['success' => true, 'data' => []]);
            }

            if ($objectConfiguration && $action === 'update') {
                $objectConfiguration->setValues($data);
                $objectConfiguration->save();

                return $this->jsonResponse(['data' => $objectConfiguration, 'success' => true]);
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

            return $this->jsonResponse(['data' => $objectConfigurations, 'success' => true, 'total' => $list->getTotalCount()]);
        }

        return $this->jsonResponse(['success' => false]);
    }
}
