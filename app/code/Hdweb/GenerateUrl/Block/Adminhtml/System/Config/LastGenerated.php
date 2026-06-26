<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Block\Adminhtml\System\Config;

use Hdweb\GenerateUrl\Helper\Data;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;

class LastGenerated extends Field
{
    /**
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        private readonly Data $helper,
        private readonly StoreManagerInterface $storeManager,
        \Magento\Backend\Block\Template\Context $context,
        array $data = [],
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $storeId = null;
        $storeParam = $this->getRequest()->getParam('store');
        
        if ($storeParam !== null) {
            $storeId = (int) $storeParam;
        }

        $lastGenerated = $this->helper->getConfigValue(
            Data::XML_PATH_LAST_GENERATED_AT,
            $storeId
        );

        if (!$lastGenerated) {
            $lastGenerated = __('Never');
        } else {
            $lastGenerated = (string) $lastGenerated;
        }

        $html = '<div style="padding: 5px 0;">';
        $html .= '<strong>' . $this->escapeHtml($lastGenerated) . '</strong>';
        $html .= '</div>';

        return $html;
    }
}

