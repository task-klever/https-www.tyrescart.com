<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public const XML_PATH_ENABLE = 'generateurl/general/enable';
    public const XML_PATH_COMPANY_NAME = 'generateurl/general/company_name';
    public const XML_PATH_COMPANY_DESCRIPTION = 'generateurl/general/company_description';
    public const XML_PATH_ADDITIONAL_INFORMATION = 'generateurl/general/additional_information';
    public const XML_PATH_FILE_PATH = 'generateurl/general/file_path';

    public const XML_PATH_ENABLE_PRODUCTS = 'generateurl/products/enable_products';
    public const XML_PATH_PRODUCT_CONTENT_FIELDS = 'generateurl/products/product_content_fields';
    public const XML_PATH_EXCLUDE_PRODUCT_IDS = 'generateurl/products/exclude_product_ids';
    public const XML_PATH_EXCLUDE_PRODUCT_SKUS = 'generateurl/products/exclude_product_skus';
    public const XML_PATH_PRODUCT_SORT_ORDER = 'generateurl/products/product_sort_order';

    public const XML_PATH_ENABLE_CATEGORIES = 'generateurl/categories/enable_categories';
    public const XML_PATH_CATEGORY_CONTENT_FIELDS = 'generateurl/categories/category_content_fields';
    public const XML_PATH_EXCLUDE_CATEGORY_IDS = 'generateurl/categories/exclude_category_ids';
    public const XML_PATH_CATEGORY_SORT_ORDER = 'generateurl/categories/category_sort_order';

    public const XML_PATH_ENABLE_CMS = 'generateurl/cms/enable_cms';
    public const XML_PATH_CMS_CONTENT_FIELDS = 'generateurl/cms/cms_content_fields';
    public const XML_PATH_EXCLUDE_CMS_PAGES = 'generateurl/cms/exclude_cms_pages';
    public const XML_PATH_CMS_SORT_ORDER = 'generateurl/cms/cms_sort_order';
    public const XML_PATH_RESTRICTED_PAGES = 'generateurl/cms/restricted_pages';
    public const XML_PATH_ADDITIONAL_PAGES = 'generateurl/cms/additional_pages';

    public const XML_PATH_ENABLE_BLOG = 'generateurl/blog/enable_blog';
    public const XML_PATH_BLOG_CONTENT_FIELDS = 'generateurl/blog/blog_content_fields';
    public const XML_PATH_EXCLUDE_BLOG_IDS = 'generateurl/blog/exclude_blog_ids';
    public const XML_PATH_BLOG_SORT_ORDER = 'generateurl/blog/blog_sort_order';

    public const XML_PATH_ENABLE_BRAND = 'generateurl/brand/enable_brand';
    public const XML_PATH_BRAND_CONTENT_FIELDS = 'generateurl/brand/brand_content_fields';
    public const XML_PATH_EXCLUDE_BRAND_IDS = 'generateurl/brand/exclude_brand_ids';
    public const XML_PATH_BRAND_SORT_ORDER = 'generateurl/brand/brand_sort_order';

    public const XML_PATH_ENABLE_VEHICLE = 'generateurl/vehicle/enable_vehicle';
    public const XML_PATH_VEHICLE_CONTENT_FIELDS = 'generateurl/vehicle/vehicle_content_fields';
    public const XML_PATH_EXCLUDE_VEHICLE_IDS = 'generateurl/vehicle/exclude_vehicle_ids';
    public const XML_PATH_EXCLUDE_VEHICLE_MAKES = 'generateurl/vehicle/exclude_vehicle_makes';
    public const XML_PATH_VEHICLE_SORT_ORDER = 'generateurl/vehicle/vehicle_sort_order';

    public const XML_PATH_FILE_STRUCTURE_STYLE = 'generateurl/file_management/file_structure_style';
    public const XML_PATH_GENERATION_FREQUENCY = 'generateurl/file_management/generation_frequency';
    public const XML_PATH_SCHEDULED_START_TIME = 'generateurl/file_management/scheduled_start_time';
    public const XML_PATH_LAST_GENERATED_AT = 'generateurl/file_management/last_generated_at';

    /**
     * @param string $path
     * @param int|null $storeId
     * @return mixed
     */
    public function getConfigValue(string $path, ?int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfigValue(self::XML_PATH_ENABLE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getFilePath(?int $storeId = null): string
    {
        return (string) ($this->getConfigValue(self::XML_PATH_FILE_PATH, $storeId) ?: 'llms');
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isProductsEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfigValue(self::XML_PATH_ENABLE_PRODUCTS, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCategoriesEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfigValue(self::XML_PATH_ENABLE_CATEGORIES, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCmsEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfigValue(self::XML_PATH_ENABLE_CMS, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isBlogEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfigValue(self::XML_PATH_ENABLE_BLOG, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isBrandEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfigValue(self::XML_PATH_ENABLE_BRAND, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isVehicleEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfigValue(self::XML_PATH_ENABLE_VEHICLE, $storeId);
    }

}

