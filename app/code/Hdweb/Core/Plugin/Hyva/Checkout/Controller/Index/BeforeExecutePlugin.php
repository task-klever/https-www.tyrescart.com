<?php

declare(strict_types=1);

namespace Hdweb\Core\Plugin\Hyva\Checkout\Controller\Index;

use Hyva\Checkout\Controller\Index\Index;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Response\Http;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;

class BeforeExecutePlugin
{
    protected LoggerInterface $logger;
    protected SessionCheckout $sessionCheckout;
    protected ManagerInterface $messageManager;
    protected ScopeConfigInterface $scopeConfig;
    protected UrlInterface $urlBuilder;

    public function __construct(
        LoggerInterface $logger,
        SessionCheckout $sessionCheckout,
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder
    ) {
        $this->logger = $logger;
        $this->sessionCheckout = $sessionCheckout;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Before plugin for Hyva Checkout execute
     *
     * @param Index $subject
     * @throws LocalizedException
     */
    public function beforeExecute(Index $subject): void
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->sessionCheckout->getQuote();

        // Only run this check if the quote actually has items
        if ($quote->hasItems()) {
            if (
                $quote->getPickupStore() == '' ||
                $quote->getPickupDate() == '' ||
                $quote->getPickupTime() == ''
            ) {
                $displayPage = $this->scopeConfig->getValue(
                    'ecomteck_storelocator/installer/display_page',
                    ScopeInterface::SCOPE_STORE
                );

                $response = $subject->getResponse();

                if ($displayPage === 'reference_cart') {
                    $this->messageManager->addErrorMessage(__('Please select installer and time slot.'));
                    $response->setRedirect($this->urlBuilder->getUrl('storelocator', ['ref' => 'cart']));
                } else {
                    $this->messageManager->addErrorMessage(__('Please select mode of delivery.'));
                    $response->setRedirect($this->urlBuilder->getUrl('checkout/cart'));
                }
                return;
            }
        }
    }
}