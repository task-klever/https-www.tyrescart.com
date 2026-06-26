<?php

namespace MGS\Brand\Controller\Brand;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use MGS\Brand\Helper\Data as Helper;


class Patternview extends \Magento\Framework\App\Action\Action
{
    /**
     * @type \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @type MGS\Brand\Helper\Data
     */
    protected $helper;

    /** @var \Magento\Catalog\Api\CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * @type \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @type \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @type \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param \Magento\Framework\App\Action\Context               $context
     * @param \Magento\Framework\View\Result\PageFactory          $resultPageFactory
     * @param \Mageplaza\Shopbybrand\Helper\Data                  $helper
     * @param \Magento\Framework\Registry                         $coreRegistry
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface    $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface          $storeManager
     * @param \Magento\Framework\Json\Helper\Data                 $jsonHelper
     */
    public function __construct(Context $context,
        PageFactory $resultPageFactory,
        Helper $helper,
        Registry $coreRegistry,
        ForwardFactory $resultForwardFactory,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_jsonHelper = $jsonHelper;
        $this->_storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;

        parent::__construct($context);
    }
    /**
     * @return bool
     */
    protected function _initPattern()
    {
        $urlKey = $this->getRequest()->getParam('pattern_key');
        $currentStoreId = $this->_storeManager->getStore()->getId();
        
        if (!$urlKey) {
            return false;
        }
        
        $currentPattern = false;
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $brandPatternObj = $objectManager->get('MGS\Brand\Model\PatternmanagementFactory');
            $brandPatternCollection = $brandPatternObj->create()->getCollection()
                                    ->addFieldToFilter('url_key', $urlKey)
                                    /* ->addFieldToFilter('store_id', $currentStoreId) */;
            foreach ($brandPatternCollection as $pattern) {
                if($pattern->getUrlKey() == $urlKey) {
                    $currentPattern = $pattern;
                    break;
                }
            }
            
        } catch (NoSuchEntityException $e) {
            return false;
        }
        $this->_coreRegistry->register('current_pattern', $currentPattern);

        return $currentPattern;
    }
    public function execute()
    {
        $pattern = $this->_initPattern();
        if ($pattern) {
            $page = $this->resultPageFactory->create();
            $page->getConfig()->addBodyClass('page-products');

            $title = $pattern->getMetaTitle();
            if ($title) {
                $page->getConfig()->getTitle()->set($title);
            }
            $description = $pattern->getMetaDescription();
            if ($description) {
                $page->getConfig()->setDescription($description);
            }
            $keywords = $pattern->getMetaKeywords();
            if ($keywords) {
                $page->getConfig()->setKeywords($keywords);
            }
            return $page;
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        
    }
}