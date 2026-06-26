<?php

namespace NetworkInternational\NGenius\Model\Magewire\Payment;

use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Message\MessageInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magewirephp\Magewire\Component;

class PlaceOrderService extends AbstractPlaceOrderService
{

    private Session $checkoutSession;

    protected $_checkoutHelper;

    public function __construct(
        CartManagementInterface $cartManagement,
        Session $checkoutSession
    ) {
        parent::__construct($cartManagement);

        $this->checkoutSession = $checkoutSession;
    }

    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {    
        return '/networkinternational/ngeniusonline/redirect';
    }
}