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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ecomteck\StoreLocator\Api\StoresRepositoryInterface;
use Ecomteck\StoreLocator\Controller\Adminhtml\Stores;
use Ecomteck\StoreLocator\Model\Stores as StoresModel;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory;
use Ecomteck\StoreLocator\Api\Data\StoresInterfaceFactory;

abstract class MassAction extends Stores
{
    /**
     * @var Filter
     */
    public $filter;
    /**
     * @var CollectionFactory
     */
    public $collectionFactory;
    /**
     * @var string
     */
    public $successMessage;
    /**
     * @var string
     */
    public $errorMessage;

    /**
     * @param Registry $registry
     * @param StoresRepositoryInterface $storesRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param StoresInterfaceFactory $storesFactory
     * @param $successMessage
     * @param $errorMessage
     */
    public function __construct(
        Registry $registry,
        StoresRepositoryInterface $storesRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        StoresInterfaceFactory $storesFactory,
        $successMessage,
        $errorMessage
    ) {
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->successMessage    = $successMessage;
        $this->errorMessage      = $errorMessage;
        parent::__construct($registry, $storesRepository,$storesFactory, $resultPageFactory, $dateFilter, $context);
    }

    /**
     * @param StoresModel $stores
     * @return mixed
     */
    public abstract function massAction(StoresModel $stores);

    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $stores) {
                $this->massAction($stores);
            }
            $this->messageManager->addSuccessMessage(__($this->successMessage, $collectionSize));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($this->errorMessage));
        }
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('storelocator/*/index');
        return $redirectResult;
    }
}
