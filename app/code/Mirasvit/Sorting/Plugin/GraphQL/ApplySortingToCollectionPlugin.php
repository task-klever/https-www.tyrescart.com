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

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Select;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Repository\CriterionRepository;
use Mirasvit\Sorting\Service\CriteriaApplierService;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsCompositeProcessor;

/**
 * @see \Magento\Catalog\Model\ResourceModel\Product\Collection::addAttributeToSort()
 * @see \Magento\Catalog\Model\ResourceModel\Product\Collection::setOrder()
 * @see \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::addAttributeToSort()
 * @see \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::addAttributeToSort()
 * @see \Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\Collection::addAttributeToSort()
 * @see \Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\Collection::setOrder()
 * @see \Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\SearchCollection::addAttributeToSort()
 * @see \Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\SearchCollection::setOrder()
 * @see \Magento\Framework\GraphQl\Query\Resolver\ArgumentsCompositeProcessor::process()
 */
class ApplySortingToCollectionPlugin
{
    /**
     * @var int
     */
    static  $increment = 1;

    private $criterionRepository;

    private $criteriaApplierService;

    public function __construct(
        CriteriaApplierService $criteriaApplierService,
        CriterionRepository $criterionRepository
    ) {
        $this->criterionRepository    = $criterionRepository;
        $this->criteriaApplierService = $criteriaApplierService;
    }

    /**
     * @param Collection    $collection
     * @param string|array  $attribute
     * @param string        $dir
     *
     * @return array
     */
    public function beforeAddAttributeToSort(Collection $collection, $attribute, $dir = Select::SQL_DESC)
    {
        return $this->beforeSetOrder($collection, $attribute, $dir);
    }

    /**
     * Apply sort criteria to collection.
     *
     * @param Collection    $collection
     * @param string|array  $attribute
     * @param string        $dir
     *
     * @return array
     */
    public function beforeSetOrder(Collection $collection, $attribute, $dir = Select::SQL_DESC)
    {
        if (is_array($attribute)) {
            if (isset($attribute['mst_sort'])) {
                $dir       = $attribute['mst_sort']['dir'];
                $attribute = $attribute['mst_sort']['code'];
            } else {
                $dir       = array_values($attribute)[0];
                $attribute = array_keys($attribute)[0];
            }
        }

        self::$increment++;

        if (!$collection->getFlag('increment')) {
            $collection->setFlag('increment', self::$increment);
        }

        if ($collection->getFlag($attribute)) { #already applied
            return [$attribute, $dir];
        }

        $collection->setFlag($attribute, true);

        $this->criteriaApplierService->setGlobalRankingFactors($collection);

        $criterion = $attribute ? $this->criterionRepository->getByCode($attribute) : $this->criteriaApplierService->getDefaultCriterion();

        if ($criterion) {
            $this->criteriaApplierService->setCriterion($collection, $criterion, $dir);

            if (!$collection->getFlag($criterion->getCode())) {
                $collection->setFlag($criterion->getCode(), true);
            }
        }

        if (!$collection->isLoaded()) {
            $this->criteriaApplierService->sortCollection($collection);
        }

        return [$attribute, $dir];
    }

    /**
     * @param object $subject
     * @param string $fieldName
     * @param array  $args
     *
     * @return array
     */

    public function beforeProcess($subject, string $fieldName, array $args)
    {
        if (isset($args['sort']['mst_sort'])) {
            $attribute = $args['sort']['mst_sort']['code'];

            if (!$attribute) {
                $defaultCriterion = $this->criteriaApplierService->getDefaultCriterion();

                $attribute = $defaultCriterion ? $defaultCriterion->getCode() : null;
            }

            $dir = $args['sort']['mst_sort']['dir'];
            $args['sort'] = [];
            $args['sort']['sorting_global'] = 'DESC';
            $args['sort'][$attribute] = $dir;
        }

        return[$fieldName, $args];
    }
}
