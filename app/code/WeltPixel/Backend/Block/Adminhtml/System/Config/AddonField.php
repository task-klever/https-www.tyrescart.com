<?php
namespace WeltPixel\Backend\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\ObjectManager;

/**
 * Class AddonField
 * @package WeltPixel\Backend\Block\Adminhtml\System\Config
 */
class AddonField extends Field
{
    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $elementData = $element->getOriginalData();
        $mdName = $elementData['button_label'] ?? '';

        if ($mdName) {
            $lcModel = ObjectManager::getInstance()->get(\WeltPixel\Backend\Model\License::class);
            $lcValue = $lcModel->getMdInfVl($mdName);
            $userFriendlyModulenames = $lcModel->getUserFriendlyModuleNames();

            if (!$lcValue) {
                return '<td class="value"><i>You Must Have a Valid License for module <b>' . $userFriendlyModulenames[$mdName] . '</b>  to access this option.</i></td>';
            }
        }

        return parent::_renderValue($element);
    }

}
