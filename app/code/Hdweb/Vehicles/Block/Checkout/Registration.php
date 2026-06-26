<?php

namespace Hdweb\Vehicles\Block\Checkout;

class Registration extends \Magento\Checkout\Block\Registration
{
    
    public function isCustomerLoggedIn()
    {
        // Custom logic

        return $this->customerSession->isLoggedIn();

        //return parent::customerSession->isLoggedIn();
    }
}
