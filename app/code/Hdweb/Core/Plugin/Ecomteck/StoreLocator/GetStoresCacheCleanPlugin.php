<?php

declare(strict_types=1);

namespace Hdweb\Core\Plugin\Ecomteck\StoreLocator;

use Ecomteck\StoreLocator\Controller\Ajax\GetStores;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Clean FPC and block_html cache after installer type changes (GetStores resets quote data),
 * so the checkout page always renders fresh installer data.
 */
class GetStoresCacheCleanPlugin
{
    private TypeListInterface $cacheTypeList;

    public function __construct(TypeListInterface $cacheTypeList)
    {
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * After GetStores execute — clean page caches so checkout reflects updated installer.
     *
     * @param GetStores $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterExecute(GetStores $subject, $result)
    {
        $this->cacheTypeList->cleanType('full_page');
        $this->cacheTypeList->cleanType('block_html');

        return $result;
    }
}
