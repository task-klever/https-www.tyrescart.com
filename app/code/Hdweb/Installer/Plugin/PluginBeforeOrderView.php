<?php

namespace Hdweb\Installer\Plugin;

class PluginBeforeOrderView
{
    protected $_storeManager;
    protected $_order;
    protected $_backendUrl;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order $order,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->_storeManager = $storeManager;
        $this->_order        = $order;
        $this->_backendUrl   = $backendUrl;
    }

    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {

        $orderStatus   = $this->_order->load($view->getOrderId())->getStatus();
        $SalesPersonId = $this->_order->load($view->getOrderId())->getSalesPersonId();
        
        $view->addButton(
            'installer_mail_button',
            [
                'label'   => __('Notify Installer'),
                'class'   => 'installer-button',
                'onclick' => "setLocation('javascript:;')",
            ]
        );

        $view->addButton(
            'notify_customer_button',
            [
                'label'   => __('Notify Customer'),
                'class'   => 'notify-customer-button',
                'onclick' => "setLocation('javascript:;')",
            ]
        );

        /* Commented out - Edit Installer button replaced by Delivery Information Edit
        $view->addButton(
            'edit_installer_button',
            [
                'label'   => __('Edit Installer'),
                'class'   => 'edit-installer-button',
                'onclick' => "setLocation('javascript:;')",
            ]
        );
        */

         if (isset($SalesPersonId) && !empty($SalesPersonId)) {
            $saleslable = 'Update Salesperson';
        } else {
            $saleslable = 'Assign Salesperson';
        }

        $view->addButton(
            'assign_sales_person',
            [
                'label'   => __($saleslable),
                'class'   => 'assign_sales_person',
                'onclick' => "setLocation('javascript:;')",
            ]
        );
        
        return null;
    }

}
