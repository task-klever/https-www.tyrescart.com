<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_StoreLocator
 * @copyright   Copyright (c) 2018 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */
namespace Ecomteck\StoreLocator\Block\Adminhtml\System\Config\Countries;
class Countries extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $countryHelper = $objectManager->create(\Magento\Directory\Model\Config\Source\Country::class);
        $countries = $countryHelper->toOptionArray();
        if (!$this->getOptions()) {
            foreach ($countries as $country) {
                $this->addOption($country['value'], addslashes($country['label']));
            }
        }
        return parent::_toHtml();
    }
}