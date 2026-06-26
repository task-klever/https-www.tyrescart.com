<?php

namespace Tamara\Checkout\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Sales\Api\OrderRepositoryInterface;
use Tamara\Checkout\Api\OrderRepositoryInterface as TamaraOrderRepository;
use Tamara\Checkout\Gateway\Config\BaseConfig;
use Tamara\Checkout\Model\Helper\CartHelper;

class Success extends Action
{
    protected $_pageFactory;
    protected $orderRepository;
    protected $config;
    protected $tamaraOrderRepository;
    /**
     * @var CartHelper;
     */
    private $cartHelper;
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var \Tamara\Checkout\Model\Adapter\TamaraAdapterFactory
     */
    private $tamaraAdapterFactory;

    protected $tamaraHelper;

    /**
     * @var \Tamara\Checkout\Helper\Transaction
     */
    protected $tamaraTransactionHelper;

    protected $tamaraOrderAuthorizationHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        CartHelper $cartHelper,
        OrderRepositoryInterface $orderRepository,
        BaseConfig $config,
        Session $checkoutSession,
        TamaraOrderRepository $tamaraOrderRepository,
        \Tamara\Checkout\Model\Adapter\TamaraAdapterFactory $tamaraAdapterFactory,
        \Tamara\Checkout\Helper\AbstractData $tamaraHelper,
        \Tamara\Checkout\Helper\Transaction $tamaraTransactionHelper,
        \Tamara\Checkout\Helper\OrderAuthorization $tamaraOrderAuthorizationHelper
    ) {
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
        $this->cartHelper = $cartHelper;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->config = $config;
        $this->tamaraOrderRepository = $tamaraOrderRepository;
        $this->tamaraAdapterFactory = $tamaraAdapterFactory;
        $this->tamaraHelper = $tamaraHelper;
        $this->tamaraTransactionHelper = $tamaraTransactionHelper;
        $this->tamaraOrderAuthorizationHelper = $tamaraOrderAuthorizationHelper;
    }

    public function execute()
    {
        /**
         * @var \Magento\Payment\Model\Method\Logger $logger
         */
        $logger = $this->_objectManager->get('TamaraCheckoutLogger');
        try {
            $orderId = $this->_request->getParam('order_id', 0);

            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);
            $storeId = $order->getStoreId();
            $tamaraOrder = $this->tamaraOrderRepository->getTamaraOrderByOrderId($orderId);
            $isAllowed = false;
            $magentoOrderState = $order->getState();
            if ($magentoOrderState == \Magento\Sales\Model\Order::STATE_NEW) {
                $isAllowed = true;
            }
            if ($magentoOrderState == \Magento\Sales\Model\Order::STATE_PROCESSING || $magentoOrderState == \Magento\Sales\Model\Order::STATE_COMPLETE) {
                if ($tamaraOrder->getIsAuthorised()) {
                    $isAllowed = true;
                }
            }
            if (!$isAllowed) {
                return $this->redirectToCartPage();
            }
        } catch (\Exception $exception) {
            return $this->redirectToCartPage();
        }
        try {
            if (!(bool) $tamaraOrder->getIsAuthorised()) {

                //authorize order
                $adapter = $this->tamaraAdapterFactory->create($storeId);
                $client = $adapter->getClient();
                $remoteOrder = $client->getOrder(new \Tamara\Request\Order\GetOrderRequest($tamaraOrder->getTamaraOrderId()));
                $this->tamaraOrderAuthorizationHelper->authorizeOrder($order, $tamaraOrder, $storeId, $remoteOrder);
            }
        } catch (\Exception $e) {
            $logger->debug(['Tamara - Error when authorize order' => $e->getMessage()], null, true);
        }

        if (!empty($merchantSuccessUrl = $this->config->getMerchantSuccessUrl($storeId))) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($merchantSuccessUrl);
            return $resultRedirect;
        }

        if ($this->config->useMagentoCheckoutSuccessPage($storeId)) {
            return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success/');
        }

        //dispatch event onepage
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            [
                'order_ids' => [$orderId],
                'order' => $order
            ]
        );

        $quoteId = $this->checkoutSession->getQuoteId();
        if ($quoteId) {
            $this->cartHelper->removeCartAfterSuccess($quoteId);
        }

        $page = $this->_pageFactory->create();
        $block = $page->getLayout()->getBlock('tamara_success');
        $block->setData('order_id', $orderId);
        $block->setData('order_increment_id', $order->getIncrementId());
        return $page;
    }

    public function redirectToCartPage() {
        $this->_redirect('checkout/cart');
        return $this->getResponse()->sendResponse();
    }
}
