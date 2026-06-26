<?php

namespace TotalPay\Gateway\Model\Data;

use TotalPay\Gateway\Api\Data\PaymentInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 *
 */
class Payment extends AbstractExtensibleObject implements PaymentInterface
{
    /**
     * @param int $value
     * @return self
     * @api
     */
    public function setOrderId($value)
    {
        return $this->setData(self::ORDER_ID, $value);
    }

    /**
     * @return string
     * @api
     */
    public function getObject()
    {
        return 'payment';
    }

    /**
     * @return int
     * @api
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * @return string|null
     * @api
     *
     */
    public function getRedirectPaymentUrl()
    {
        return $this->_get(self::REDIRECT_TOTALPAY_URL);
    }

    /**
     * @param string $value
     *
     * @return self
     * @api
     *
     */
    public function setRedirectPaymentUrl($value)
    {
        return $this->setData(self::REDIRECT_TOTALPAY_URL, $value);
    }

}
