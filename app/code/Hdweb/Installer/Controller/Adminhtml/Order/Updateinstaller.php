<?php

namespace Hdweb\Installer\Controller\Adminhtml\Order;
use Magento\Store\Model\ScopeInterface;

class Updateinstaller extends \Magento\Backend\App\Action
{
    protected $_order;
    protected $_scopeConfig;
    protected $ecomtechStoreLocator;
    protected $orderStatusRepository;
    protected $authSession;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomteck\StoreLocator\Model\Stores $ecomtechStoreLocator,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        \Magento\Backend\Model\Auth\Session $authSession
        )
    {
            parent::__construct($context);
            $this->_order = $order; 
            $this->_scopeConfig = $scopeConfig;
            $this->ecomtechStoreLocator = $ecomtechStoreLocator;
            $this->orderStatusRepository = $orderStatusRepository;
            $this->authSession = $authSession;
    }
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order_id = $this->getRequest()->getParam('order_id');
        $installer_id = $this->getRequest()->getParam('installer');

        $order = $this->_order->load($order_id);
        $oldInstallerId = $order->getPickupStore();
        $oldInstaller = $this->ecomtechStoreLocator->load($oldInstallerId);
        $oldInstallerName = $oldInstaller['name'];
        
        $newInstaller = $this->ecomtechStoreLocator->load($installer_id);
        $newInstallerName = $newInstaller['name'];
        
        if(isset($installer_id) && !empty($installer_id)) {
            //$order = $this->_order->load($order_id);   // somehow pickup_store value wasn't changing using this that's why objectManager
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);
            $order->setPickupStore($installer_id);
            //$order->addStatusHistoryComment('Installer is changed from '.$oldInstallerName.' to '.$newInstallerName);
            $adminUser = $this->authSession->getUser();
            $comment = $order->addStatusHistoryComment(
                'Installer is changed from '.$oldInstallerName.' to '.$newInstallerName. ' - BY ' . $adminUser->getFirstname(). ' '.$adminUser->getLastname()
            );
            try {
                $orderHistory = $this->orderStatusRepository->save($comment);
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
            $order->save();
        }

        $this->messageManager->addSuccess(__('Installer changed successfully.'));
        $this->_redirect('sales/order/view', array('order_id' => $order_id)); 
    }
}