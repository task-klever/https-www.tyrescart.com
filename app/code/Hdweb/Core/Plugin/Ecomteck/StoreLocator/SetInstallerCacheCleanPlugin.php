<?php

declare(strict_types=1);

namespace Hdweb\Core\Plugin\Ecomteck\StoreLocator;

use Ecomteck\StoreLocator\Controller\Ajax\SetInstaller;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Clean FPC and block_html cache after installer selection changes,
 * so the checkout page always renders fresh installer data.
 */
class SetInstallerCacheCleanPlugin
{
    private TypeListInterface $cacheTypeList;

    public function __construct(TypeListInterface $cacheTypeList)
    {
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * After SetInstaller execute — clean page caches so checkout reflects updated installer.
     *
     * @param SetInstaller $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterExecute(SetInstaller $subject, $result)
    {
        $this->cacheTypeList->cleanType('full_page');
        $this->cacheTypeList->cleanType('block_html');

        return $result;
    }
}
