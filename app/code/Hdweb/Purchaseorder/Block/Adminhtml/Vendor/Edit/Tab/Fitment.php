<?php

namespace Hdweb\Purchaseorder\Block\Adminhtml\Vendor\Edit\Tab;

class Fitment extends \Magento\Backend\Block\Widget\Form\Generic implements
\Magento\Backend\Block\Widget\Tab\TabInterface {

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, array $data = []
    ) {

        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getFormHtml() {
        //$arr = explode("</form>", parent::getFormHtml());
        //$html = $arr[0];
        $html = '';

        $html .= $this->getLayout()->createBlock(
                        'Hdweb\Purchaseorder\Block\Adminhtml\Vendor\Fitment'
                )
                ->toHtml();
        /* $html .= $this->getLayout()->createBlock(
                        'Hdweb\Purchaseorder\Block\Adminhtml\Vendor\Fitment\Grid'
                )->toHtml(); */

        $html .= '</form>';

        return $html;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel() {
        return __('Fitment Charges');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle() {
        return __('Fitment Charges');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab() {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden() {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId) {
        return $this->_authorization->isAllowed($resourceId);
    }

}
