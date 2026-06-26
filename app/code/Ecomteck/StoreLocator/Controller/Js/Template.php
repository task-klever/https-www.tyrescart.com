<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecomteck\StoreLocator\Controller\Js;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Template extends Action
{
    /**
     * @var string
     */
    const TEMPLATE_CONFIG_PATH = 'ecomteck_storelocator/template/%s';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;
    
    /** @var \Magento\Framework\View\Result\PageFactory  */
    public $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * Load the page defined in view/frontend/layout/storelocator_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $templateId = $this->getRequest()->getParam('template');
        $template = $this->getTemplate($templateId);
        $this->getResponse()
        ->setContent($template);
        return;
    }

    protected function getTemplate($templateId) 
    {
        $template = $this->scopeConfig->getValue(sprintf(static::TEMPLATE_CONFIG_PATH,$templateId),ScopeInterface::SCOPE_STORE);
        return $template;
    }
}