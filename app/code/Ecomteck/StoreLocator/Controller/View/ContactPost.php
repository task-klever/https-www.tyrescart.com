<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @author    Ecomteck <ecomteck@gmail.com>
 * @copyright 2017 Ecomteck
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Ecomteck\StoreLocator\Controller\View;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Ecomteck\StoreLocator\Api\StoresRepositoryInterface;
use Ecomteck\StoreLocator\Helper\Contact as ContactHelper;
use Ecomteck\StoreLocator\Model\Stores\ContactFormFactory;

/**
 * Store Contact form submit.
 *
 * @category Ecomteck
 * @package  Ecomteck_StoreLocator
 * @author   Ecomteck <ecomteck@gmail.com>
 */
class ContactPost extends Action
{
    /**
     * Page factory.
     *
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoresRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Ecomteck\StoreLocator\Model\Retailer\ContactFormFactory
     */
    private $contactFormFactory;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Ecomteck\StoreLocator\Helper\Contact
     */
    private $contactHelper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    private $forwardFactory;

    /**
     * Constructor.
     *
     * @param Context                     $context                Application Context
     * @param PageFactory                 $pageFactory            Result Page Factory
     * @param StoreManagerInterface       $storeManager           Store Manager
     * @param StoresRepositoryInterface $storeRepository     Retailer Repository
     * @param DataPersistorInterface      $dataPersistorInterface Data Persistor
     * @param ContactFormFactory          $contactFormFactory     Contact Form Factory
     * @param ContactHelper               $contactHelper          Contact Helper
     * @param ForwardFactory              $forwardFactory         Forward Factory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        StoreManagerInterface $storeManager,
        StoresRepositoryInterface $storeRepository,
        DataPersistorInterface $dataPersistorInterface,
        ContactFormFactory $contactFormFactory,
        ContactHelper $contactHelper,
        ForwardFactory $forwardFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory  = $pageFactory;
        $this->storeManager       = $storeManager;
        $this->storeRepository = $storeRepository;
        $this->contactFormFactory = $contactFormFactory;
        $this->dataPersistor      = $dataPersistorInterface;
        $this->contactHelper      = $contactHelper;
        $this->forwardFactory     = $forwardFactory;
    }

    /**
     * Post user question
     *
     * @throws \Exception
     * @return void|ResultInterface
     */
    public function execute()
    {
        $postData   = $this->getRequest()->getPostValue();
        $storeId = $this->getRequest()->getParam('id');
        $store   = $this->storeRepository->getById($storeId);
        if (!$store->getId() || !$this->contactHelper->canDisplayContactForm($store)) {
            $resultForward = $this->forwardFactory->create();
            return $resultForward->forward('noroute');
        }

        try {
            if (!$postData) {
                $this->_redirect($store->getStoresUrl());

                return;
            }

            $contactForm = $this->contactFormFactory->create(['store' => $store, 'data' => $postData]);
            $contactForm->send();

            $this->messageManager->addSuccessMessage(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->dataPersistor->clear('contact_store');
            $this->_redirect($store->getStoresUrl());

            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
            $this->dataPersistor->set('contact_store', $postData);
            $this->_redirect($store->getStoresUrl());

            return;
        }
    }
}
