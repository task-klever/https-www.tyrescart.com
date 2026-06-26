<?php
/**
 * Ecomteck_StorePickup Magento Extension
 *
 * @category    Ecomteck
 * @package     Ecomteck_StorePickup
 * @author      Ecomteck <ecomteck@gmail.com>
 * @website    http://www.ecomteck.com
 */

namespace Ecomteck\StorePickup\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class BlockObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_date;
    protected $_coreTemplate;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\View\Element\Template $coreTemplate
    )
    {
        $this->_date = $date;
        $this->_coreTemplate = $coreTemplate;
    }

    public function execute(EventObserver $observer)
    {
        if($observer->getElementName() == 'order_shipping_view') {
            $shippingInfoBlock = $observer->getLayout()->getBlock($observer->getElementName());
            $order = $shippingInfoBlock->getOrder();

            if($order->getShippingMethod() != 'storepickup_storepickup') {
                return $this;
            }

            $formattedDate = $this->_date->formatDate($order->getPickupDate(), \IntlDateFormatter::MEDIUM);
            $pickupInfo = $this->_coreTemplate
                ->setPickupDate($formattedDate)
                ->setPickupTime($order->getPickupTime())
                ->setPickupStore($order->getPickupStore())
                ->setTemplate('Ecomteck_StorePickup::order/view/pickup-info.phtml')
                ->toHtml();
            $html = $observer->getTransport()->getOutput() . $pickupInfo;
            $observer->getTransport()->setOutput($html);
        }
    }
}