<?php
/**
 * Notify Invoice – Save comment and send custom invoice email with PDF attachment.
 */
declare(strict_types=1);

namespace Klever\OrderActions\Controller\Adminhtml\Invoice;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Framework\App\Area;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Pdf\Invoice as PdfInvoice;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Klever\OrderActions\Model\Mail\Template\TransportBuilder;

class SendMail extends Action
{
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_invoice';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PdfInvoice
     */
    private $pdfInvoice;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var Renderer
     */
    private $addressRenderer;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $orderStatusRepository;

    /**
     * @var AuthSession
     */
    private $authSession;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        PdfInvoice $pdfInvoice,
        PaymentHelper $paymentHelper,
        Renderer $addressRenderer,
        OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        AuthSession $authSession
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->pdfInvoice = $pdfInvoice;
        $this->paymentHelper = $paymentHelper;
        $this->addressRenderer = $addressRenderer;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->authSession = $authSession;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $comment = (string) $this->getRequest()->getParam('invoice_comment', '');

        try {
            $order = $this->orderRepository->get($orderId);
            $invoice = $order->getInvoiceCollection()->setPageSize(1)->getFirstItem();

            if (!$invoice || !$invoice->getId()) {
                $this->messageManager->addErrorMessage(__('No invoice found for this order.'));
                return $this->_redirect('sales/order/view', ['order_id' => $orderId]);
            }

            /* Save comment to order status history */
            if ($comment) {
                $adminUser = $this->authSession->getUser();
                $adminName = $adminUser
                    ? $adminUser->getFirstname() . ' ' . $adminUser->getLastname()
                    : 'Admin';
                $historyComment = $order->addStatusHistoryComment(
                    'Notify - Invoice - ' . $comment . ' - BY ' . $adminName
                );
                $this->orderStatusRepository->save($historyComment);
            }

            /* Generate invoice PDF */
            $pdf = $this->pdfInvoice->getPdf([$invoice]);
            $pdfData = $pdf->render();
            $fileName = 'invoice_' . $order->getIncrementId() . '.pdf';

            /* Build formatted addresses and payment HTML */
            $formattedBillingAddress = $this->addressRenderer->format(
                $order->getBillingAddress(),
                'html'
            );
            $formattedShippingAddress = '';
            if (!$order->getIsVirtual()) {
                $formattedShippingAddress = $this->addressRenderer->format(
                    $order->getShippingAddress(),
                    'html'
                );
            }
            $paymentHtml = $this->paymentHelper
                ->getInfoBlockHtml($order->getPayment(), $order->getStoreId());

            /* Store contact info */
            $storeId = $order->getStore()->getId();
            $this->storeManager->setCurrentStore($storeId);

            $senderEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
            $senderName = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
            $from = ['email' => $senderEmail, 'name' => $senderName];

            $storeEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
            $storePhone = $this->scopeConfig->getValue('general/store_information/phone', ScopeInterface::SCOPE_STORE);
            $storeHours = $this->scopeConfig->getValue('general/store_information/hours', ScopeInterface::SCOPE_STORE);

            $customerEmail = $order->getCustomerEmail();

            $templateVars = [
                'order' => $order,
                'order_id' => $order->getId(),
                'invoice' => $invoice,
                'invoice_id' => $invoice->getId(),
                'comment' => $comment,
                'billing' => $order->getBillingAddress(),
                'payment_html' => $paymentHtml,
                'store' => $order->getStore(),
                'formattedShippingAddress' => $formattedShippingAddress,
                'formattedBillingAddress' => $formattedBillingAddress,
                'store_email' => $storeEmail,
                'store_phone' => $storePhone,
                'store_hours' => $storeHours,
                'order_data' => [
                    'customer_name' => $order->getCustomerName(),
                    'is_not_virtual' => $order->getIsNotVirtual(),
                    'email_customer_note' => $order->getEmailCustomerNote(),
                    'frontend_status_label' => $order->getFrontendStatusLabel(),
                ],
            ];

            $templateOptions = [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId,
            ];

            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('klever_orderactions_notify_invoice')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($customerEmail)
                ->addAttachment($pdfData, $fileName, 'application/pdf')
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();

            $this->messageManager->addSuccessMessage(__('Invoice email has been sent to %1.', $customerEmail));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('sales/order/view', ['order_id' => $orderId]);
    }
}
