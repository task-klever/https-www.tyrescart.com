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

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Ecomteck\StoreLocator\Api\StoresRepositoryInterface;
use Ecomteck\StoreLocator\Api\Data\StoresInterface;
use Ecomteck\StoreLocator\Api\Data\StoresInterfaceFactory;
use Ecomteck\StoreLocator\Controller\Adminhtml\Stores as StoresController;
use Ecomteck\StoreLocator\Model\Stores;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores as StoresResourceModel;

class InlineEdit extends StoresController
{
    /**
     * @var DataObjectHelper
     */
    public $dataObjectHelper;
    /**
     * @var DataObjectProcessor
     */
    public $dataObjectProcessor;
    /**
     * @var JsonFactory
     */
    public $jsonFactory;
    /**
     * @var StoresResourceModel
     */
    public $storesResourceModel;

    /**
     * @param Registry $registry
     * @param StoresRepositoryInterface $storesRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param JsonFactory $jsonFactory
     * @param StoresResourceModel $storesResourceModel
     */
    public function __construct(
        Registry $registry,
        StoresRepositoryInterface $storesRepository,
        StoresInterfaceFactory $storesFactory,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        JsonFactory $jsonFactory,
        StoresResourceModel $storesResourceModel
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper    = $dataObjectHelper;
        $this->jsonFactory         = $jsonFactory;
        $this->storesResourceModel = $storesResourceModel;
        parent::__construct($registry, $storesRepository,$storesFactory, $resultPageFactory, $dateFilter, $context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $storesId) {
            /** @var \Ecomteck\StoreLocator\Model\Stores|StoresInterface $stores */
            $stores = $this->storesRepository->getById((int)$storesId);
            try {
                //$storesData = $this->filterData($postItems[$storesId]);
                $storesData = $postItems[$storesId];
                $this->dataObjectHelper->populateWithArray($stores, $storesData , StoresInterface::class);
                $this->storesResourceModel->saveAttribute($stores, array_keys($storesData));
            } catch (LocalizedException $e) {
                $messages[] = $this->getErrorWithStoresId($stores, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithStoresId($stores, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithStoresId(
                    $stores,
                    __('Something went wrong while saving the stores.'.$e->getMessage())
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add stores id to error message
     *
     * @param Stores $stores
     * @param string $errorText
     * @return string
     */
    public function getErrorWithStoresId(Stores $stores, $errorText)
    {
        return '[Stores ID: ' . $stores->getId() . '] ' . $errorText;
    }
}
