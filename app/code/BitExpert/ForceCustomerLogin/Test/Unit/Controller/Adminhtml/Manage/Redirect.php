<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Test\Unit\Controller\Adminhtml\Manage;

/**
 * Helper class to be able to retrieve the response code set in the action classes.
 */
class Redirect extends \Magento\Backend\Model\View\Result\Redirect
{
    public function getHttpResponseCode()
    {
        return $this->httpResponseCode;
    }
}
