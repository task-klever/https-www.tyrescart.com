<?php

namespace Hdweb\Purchaseorder\Plugin;

class PluginBeforeView
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
        $po_params    = array('order_id' => $view->getOrderId(),'po_type' => 'mpo');
        $po_actionUrl = $this->_backendUrl->getUrl("purchaseorder/create/index", $po_params);
        $view->addButton(
            'generate_po',
            ['label'  => __('MPO'),
                'onclick' => "setLocation('{$po_actionUrl}')",
                'class'   => 'reset',
            ],
            -1
        );

        $pof_params    = array('order_id' => $view->getOrderId(),'fpo' => 1,'po_type' => 'fpo');
        $pof_actionUrl = $this->_backendUrl->getUrl("purchaseorder/create/index", $pof_params);
        $view->addButton(
            'generate_fpo',
            ['label'  => __('FPO'),
                'onclick' => "setLocation('{$pof_actionUrl}')",
                'class'   => 'reset',
            ],
            -1
        );

        $pof_params    = array('order_id' => $view->getOrderId(),'dpo' => 1,'po_type' => 'dpo');
        $pof_actionUrl = $this->_backendUrl->getUrl("purchaseorder/create/index", $pof_params);
        $view->addButton(
            'generate_dpo',
            ['label'  => __('DPO'),
                'onclick' => "setLocation('{$pof_actionUrl}')",
                'class'   => 'reset',
            ],
            -1
        );

        $pof_params    = array('order_id' => $view->getOrderId(),'ppo' => 1,'po_type' => 'ppo');
        $pof_actionUrl = $this->_backendUrl->getUrl("purchaseorder/create/index", $pof_params);
        $view->addButton(
            'generate_ppo',
            ['label'  => __('PPO'),
                'onclick' => "setLocation('{$pof_actionUrl}')",
                'class'   => 'reset',
            ],
            -1
        );

        /*if($this->_order->load($view->getOrderId())->getPickupLocation() != ''){
            $pickup_detail = unserialize($this->_order->load($view->getOrderId())->getPickupLocation());
            if(isset($pickup_detail['pick_type'])){
                $ppo_params    = array('order_id' => $view->getOrderId(),'ppo' => 1,'po_type' => 'ppo');
                $pickupPurchase_actionUrl = $this->_backendUrl->getUrl("purchaseorder/create/index", $ppo_params);
                $view->addButton(
                    'generate_ppo',
                    ['label'  => __('PPO'),
                        'onclick' => "setLocation('{$pickupPurchase_actionUrl}')",
                        'class'   => 'reset',
                    ],
                    -1
                );
            }
        }*/

        return null;
    }

}
