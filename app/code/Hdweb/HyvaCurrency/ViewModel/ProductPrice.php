<?php
namespace Hdweb\HyvaCurrency\ViewModel;

use Hyva\Theme\ViewModel\ProductPrice as HyvaProductPrice;

class ProductPrice extends HyvaProductPrice
{
    /**
     * Format price with custom currency symbol
     *
     * @param float $price
     * @return string
     */
    public function formatPriceWithCustomSymbol(float $price): string
    {
        $formatted = parent::format($price);

        // Replace AED or Arabic Dirham variants with custom symbol
        return str_replace(['AED', 'د.إ.‏', 'د.إ'], 'د.إ.‏', $formatted);
    }
}
