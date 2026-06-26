<?php
/**
 * Ecomteck_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @copyright 2016 Ecomteck
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @author    Ecomteck
 */
namespace Ecomteck\StoreLocator\Controller\Adminhtml\Stores;

use Ecomteck\StoreLocator\Controller\Adminhtml\Stores;
use Ecomteck\StoreLocator\Controller\RegistryConstants;

class Edit extends Stores
{
    /**
     * Initialize current stores and set it in the registry.
     *
     * @return int
     */
    public function _initStores()
    {
        $storesId = $this->getRequest()->getParam('stores_id');
        $this->coreRegistry->register(RegistryConstants::CURRENT_STORES_ID, $storesId);
        if($storesId){
            $stores = $this->storesRepository->getById($storesId);
            $this->coreRegistry->register(RegistryConstants::CURRENT_STORES, $stores);
        } else {
            $stores = $this->storesFactory->create();
            $this->coreRegistry->register(RegistryConstants::CURRENT_STORES, $stores);
        }
        
        return $storesId;
    }

    /**
     * Edit or create stores
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $storesId = $this->_initStores();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ecomteck_StoreLocator::stores');
        $resultPage->getConfig()->getTitle()->prepend(__('StoreLocator'));
        $resultPage->addBreadcrumb(__('StoreLocator'), __('StoreLocator'), $this->getUrl('storelocator/stores'));

        if ($storesId === null) {
            $resultPage->addBreadcrumb(__('New Store'), __('New Store'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Store'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Store'), __('Edit Store'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->storesRepository->getById($storesId)->getName()
            );
        }
        return $resultPage;
    }
}
