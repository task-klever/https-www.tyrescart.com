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
namespace Ecomteck\StoreLocator\Block\Adminhtml\System\Config;
class Countries extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var Fields
     */
    protected $_countryFieldRenderer;

    /**
     * Retrieve group column renderer
     *
     * @return Customergroup
     */
    protected function _getCountryFieldRenderer()
    {
        if (!$this->_countryFieldRenderer) {
            $this->_countryFieldRenderer = $this->getLayout()->createBlock(
                \Ecomteck\StoreLocator\Block\Adminhtml\System\Config\Countries\Countries::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_countryFieldRenderer->setClass('fields_select');
        }
        return $this->_countryFieldRenderer;
    }

    protected function _prepareToRender()
    {
        $this->addColumn('country', ['label' => __('Country'),  'renderer' => $this->_getCountryFieldRenderer()]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Country');
    } 

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getCountryFieldRenderer()->calcOptionHash($row->getData('country'))] =
            'selected="selected"';
        
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}