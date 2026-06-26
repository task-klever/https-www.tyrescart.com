<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Widget\Button;

class DownloadButton extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $url = $this->getUrl('generateurl/download/index', ['store' => $storeId]);

        $button = $this->getLayout()->createBlock(Button::class)
            ->setType('button')
            ->setLabel(__('Download LLMs.txt'))
            ->setOnClick("window.location.href='" . $url . "'")
            ->setClass('action-default scalable action-save action-secondary');

        return $button->toHtml();
    }
}

