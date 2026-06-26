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
 * @package   mirasvit/module-reports
 * @version   1.6.0
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\Reports\Config\Type;


use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;

class ProductImage implements TypeInterface
{
    private $imageHelper;

    private $productRepository;

    public function __construct(
        ImageHelper $imageHelper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->imageHelper       = $imageHelper;
        $this->productRepository = $productRepository;
    }

    public function getType()
    {
        return 'image'; // this is the only column of this type
    }

    public function getAggregators()
    {
        return [AggregatorInterface::TYPE_NONE];
    }

    public function getValueType()
    {
        return self::VALUE_TYPE_STRING;
    }

    public function getJsType()
    {
        return self::JS_TYPE_HTML;
    }

    public function getJsFilterType()
    {
        return false;
    }

    public function getFormattedValue($actualValue, AggregatorInterface $aggregator)
    {
        $imageUrl = $this->imageHelper->getDefaultPlaceholderUrl();
        $product  = null;

        try {
            $product  = $this->productRepository->getById($actualValue, false);
            $image    = $this->imageHelper->init($product, 'product_listing_thumbnail');
            $imageUrl = $image->getUrl();
        } catch (\Exception $e) {}

        return (string)$imageUrl;
    }

    public function getPk($actualValue, AggregatorInterface $aggregator)
    {
        return $actualValue;
    }
}
