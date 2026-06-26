<?php
/**
 * Copyright © 2015 Hdweb. All rights reserved.
 */

namespace Hdweb\Purchaseorder\Controller\Adminhtml\Vendor;

class Index extends \Hdweb\Purchaseorder\Controller\Adminhtml\Vendor
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Hdweb_Purchaseorder::vendor');
        $resultPage->getConfig()->getTitle()->prepend(__('Vendor Details'));
        $resultPage->addBreadcrumb(__('Hdweb'), __('Hdweb'));
        $resultPage->addBreadcrumb(__('Vendor'), __('Vendor'));
        return $resultPage;
    }
}
