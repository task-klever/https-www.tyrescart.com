<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-sorting
 * @version   1.3.15
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Plugin\GraphQL;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManager;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\Sorting\Model\Config\Source\CriteriaSource;
use Mirasvit\Sorting\Service\CriteriaManagementService;

/**
 * @see \Magento\CatalogGraphQl\Model\Resolver\Category\SortFields::resolve()
 */
class AddSortingCriteriaToSortByPlugin
{
    private $criteriaSource;

    private $request;

    private $categoryRepository;

    private $storeManager;

    private $criteriaManagementService;

    public function __construct(
        CriteriaSource $criteriaSource,
        RequestInterface $request,
        CategoryRepository $categoryRepository,
        StoreManager $storeManager,
        CriteriaManagementService $criteriaManagementService
    ) {
        $this->criteriaSource            = $criteriaSource;
        $this->request                   = $request;
        $this->categoryRepository        = $categoryRepository;
        $this->storeManager              = $storeManager;
        $this->criteriaManagementService = $criteriaManagementService;
    }

    public function afterResolve($subject, array $result = []): array
    {
        if (!count($this->criteriaSource->getConfiguredSortingOptions())) {
            return $result;
        }

        $result = $this->getSortingOptions();

        // translate criterion labels
        foreach ($result['options'] as $idx => $option) {
            $result['options'][$idx]['label'] = __($option['label']);
        }

        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getSortingOptions(): array
    {
        $defaultData = [
            'default' => $this->criteriaManagementService->getDefaultCriterion()->getCode(),
            'options' => $this->criteriaSource->toOptionArray()
        ];

        $requestContent = SerializeService::decode($this->request->getContent());

        if (!$requestContent || !isset($requestContent['query'])) {
            return $defaultData;
        }

        $requestContent    = $requestContent['query'];
        $hasCategoryFilter = preg_match(
            '/products\([^\)]*filter:\s*\{\s*(category_id\s*:\s*\{[^\}]*\})[^\)]*\)/is',
            $requestContent,
            $match
        );

        if (!$hasCategoryFilter || count($match) !== 2) {
            return $defaultData;
        }

        $categoryFilter = $match[1];

        if (!preg_match('/\{\s*eq:\D*(\d*)\D*\}/is', $categoryFilter, $m) || count($m) !== 2) {
            return $defaultData;
        }

        try {
            $category = $this->categoryRepository->get(
                $m[1],
                $this->storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException $e) {
            return $defaultData;
        }

        $options = $defaultData['options'];

        if ($allowedOptions = $category->getData('available_sort_by')) {
            foreach ($options as $key => $option) {
                if (!in_array($option['value'], $allowedOptions)) {
                    unset($options[$key]);
                }
            }
        }

        return [
            'default' => $category->getData('default_sort_by') ?: $defaultData['default'],
            'options' => array_values($options)
        ];
    }
}
