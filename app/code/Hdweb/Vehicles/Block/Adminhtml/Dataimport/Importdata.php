<?php 

namespace Hdweb\Vehicles\Block\Adminhtml\Dataimport;

class Importdata extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'row_id';
        $this->_blockGroup = 'hdweb_vehicles';
        $this->_controller = 'adminhtml_dataimport';
        parent::_construct();
        $this->buttonList->remove('back');
        $this->buttonList->update('save', 'label', __('Import'));
        $this->buttonList->remove('reset');

        $this->addButton(
                    'backhome',
                    [
                        'label' => __('Back'),
                        'on_click' => sprintf("location.href = '%s';", $this->getUrl('hdweb_vehicles/items/index')),
                        'class' => 'back',
                        'level' => -2
                    ]
                );


    }


    public function getHeaderText()
    {
        return __('Import Data');
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }


    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('hdweb_vehicles/dataimport/save');
    }
}