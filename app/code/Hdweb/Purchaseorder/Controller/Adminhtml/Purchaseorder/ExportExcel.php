<?php

namespace Hdweb\Purchaseorder\Controller\Adminhtml\Purchaseorder;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportExcel extends \Magento\Backend\App\Action
{
    protected $_fileFactory;

    public function execute()
    {
        $this->_view->loadLayout(false);

        $fileName = 'Purchaseorder.xml';

        $exportBlock = $this->_view->getLayout()->createBlock('Hdweb\Purchaseorder\Block\Adminhtml\Purchaseorder\Grid');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->_fileFactory = $objectManager->create('Magento\Framework\App\Response\Http\FileFactory');

        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile(),
            DirectoryList::VAR_DIR
        );
    }
}
