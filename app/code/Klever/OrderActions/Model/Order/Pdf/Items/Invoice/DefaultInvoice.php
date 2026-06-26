<?php
/**
 * Custom Invoice PDF item renderer – removes SKU from item rows.
 */
declare(strict_types=1);

namespace Klever\OrderActions\Model\Order\Pdf\Items\Invoice;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\RtlTextHandler;

class DefaultInvoice extends \Magento\Sales\Model\Order\Pdf\Items\Invoice\DefaultInvoice
{
    /**
     * Draw item line – same as parent but without SKU block.
     *
     * @return void
     */
    public function draw()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];

        /* RTL handler – parent's prepareText() is private, so we use ObjectManager */
        $rtlTextHandler = ObjectManager::getInstance()->get(RtlTextHandler::class);

        // draw Product name
        $nameText = $rtlTextHandler->reverseRtlText(
            html_entity_decode((string)$item->getName())
        );
        $lines[0][] = [
            'text' => $this->string->split($nameText, 35, true, true),
            'feed' => 35
        ];

        // SKU removed

        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 435, 'align' => 'right'];

        // draw item Prices
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = 395;
        $feedSubtotal = $feedPrice + 170;
        foreach ($prices as $priceData) {
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedPrice, 'align' => 'right'];
                // draw Subtotal label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'right'];
                $i++;
            }
            // draw Price
            $lines[$i][] = [
                'text' => $priceData['price'],
                'feed' => $feedPrice,
                'font' => 'bold',
                'align' => 'right',
            ];
            // draw Subtotal
            $lines[$i][] = [
                'text' => $priceData['subtotal'],
                'feed' => $feedSubtotal,
                'font' => 'bold',
                'align' => 'right',
            ];
            $i++;
        }

        // draw Tax
        $lines[0][] = [
            'text' => $order->formatPriceTxt($item->getTaxAmount()),
            'feed' => 495,
            'font' => 'bold',
            'align' => 'right',
        ];

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                ];

                // Checking whether option value is not null
                if ($option['value'] !== null) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
                        $printValue = $this->filterManager->stripTags($option['value']);
                    }
                    $printValue = str_replace(PHP_EOL, ', ', $printValue);
                    $values = explode(', ', $printValue);
                    $text = [];
                    foreach ($values as $value) {
                        foreach ($this->string->split($value, 50, true, true) as $subValue) {
                            $text[] = $subValue;
                        }
                    }

                    $lines[][] = ['text' => $text, 'feed' => 40];
                }
            }
        }

        $lineBlock = ['lines' => $lines, 'height' => 20, 'shift' => 5];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }
}
