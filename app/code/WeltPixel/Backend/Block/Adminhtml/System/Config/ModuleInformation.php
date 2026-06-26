<?php
namespace WeltPixel\Backend\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class ModuleInformation
 * @package WeltPixel\Backend\Block\Adminhtml\System\Config
 */
class ModuleInformation extends Field
{
    protected $_template = 'WeltPixel_Backend::system/config/module_information.phtml';
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $elementData = $element->getOriginalData();
        $this->setData('element_data', $elementData);

        return $this->_toHtml();
    }
}
