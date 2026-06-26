<?php
/**
 * Notify Invoice – Generate and stream invoice PDF download.
 */
declare(strict_types=1);

namespace Klever\OrderActions\Controller\Adminhtml\Invoice;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Pdf\Invoice as PdfInvoice;

class DownloadPdf extends Action
{
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_invoice';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PdfInvoice
     */
    private $pdfInvoice;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        PdfInvoice $pdfInvoice,
        FileFactory $fileFactory,
        DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->pdfInvoice = $pdfInvoice;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');

        try {
            $order = $this->orderRepository->get($orderId);
            $invoices = $order->getInvoiceCollection();

            if (!$invoices->getSize()) {
                $this->messageManager->addErrorMessage(__('No invoice found for this order.'));
                return $this->_redirect('sales/order/view', ['order_id' => $orderId]);
            }

            $pdf = $this->pdfInvoice->getPdf($invoices);
            $date = $this->dateTime->date('Y-m-d_H-i-s');
            $fileName = 'invoice_' . $order->getIncrementId() . '_' . $date . '.pdf';

            return $this->fileFactory->create(
                $fileName,
                $pdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('sales/order/view', ['order_id' => $orderId]);
        }
    }
}
