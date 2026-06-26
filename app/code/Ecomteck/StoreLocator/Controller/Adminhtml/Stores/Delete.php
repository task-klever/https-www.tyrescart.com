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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Ecomteck\StoreLocator\Controller\Adminhtml\Stores;

class Delete extends Stores
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('stores_id');
        if ($id) {
            try {
                $this->storesRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The stores has been deleted.'));
                $resultRedirect->setPath('storelocator/*/');
                return $resultRedirect;
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The stores no longer exists.'));
                return $resultRedirect->setPath('storelocator/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('storelocator/stores/edit', ['stores_id' => $id]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There was a problem deleting the stores'));
                return $resultRedirect->setPath('storelocator/stores/edit', ['stores_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a stores to delete.'));
        $resultRedirect->setPath('storelocator/*/');
        return $resultRedirect;
    }
}
