<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @author    Ecomteck <ecomteck@gmail.com>
 * @author    Ecomteck <ecomteck@gmail.com>
 * @copyright 2016 Ecomteck
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Ecomteck\StoreLocator\Controller\View;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Ecomteck\StoreLocator\Api\StoresRepositoryInterface;

/**
 * Store view action (displays the retailer details page).
 *
 * @category Ecomteck
 * @package  Ecomteck_StoreLocator
 * @author   Ecomteck <ecomteck@gmail.com>
 * @author   Ecomteck <ecomteck@gmail.com>
 */
class Index extends Action
{
    /**
     * Page factory.
     *
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Forward factory.
     *
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * Core registry.
     *
     * @var Registry
     */
    private $coreRegistry;

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoresRepositoryInterface
     */
    private $storesRepository;

    /**
     * Constructor.
     *
     * @param Context                     $context            Application Context
     * @param PageFactory                 $pageFactory        Result Page Factory
     * @param ForwardFactory              $forwardFactory     Forward Factory
     * @param Registry                    $coreRegistry       Application Registry
     * @param StoreManagerInterface       $storeManager       Store Manager
     * @param StoresRepositoryInterface $retailerRepository Store Repository
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        StoresRepositoryInterface $storesRepository
    ) {
        parent::__construct($context);

        $this->resultPageFactory    = $pageFactory;
        $this->resultForwardFactory = $forwardFactory;
        $this->coreRegistry         = $coreRegistry;
        $this->storeManager         = $storeManager;
        $this->storesRepository   = $storesRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('id');
        $store   = $this->storesRepository->getById($storeId);
        if (!$store->getId()) {
            $resultForward = $this->resultForwardFactory->create();

            return $resultForward->forward('noroute');
        }

        $this->coreRegistry->register('current_store', $store);

        $resultPage =  $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(
            $store->getName()
        );
        $resultPage->getConfig()->setDescription(
            $store->getIntro()
        );
        $resultPage->getConfig()->setKeywords(
            $store->getName()
        );
        return $resultPage;
    }
}
