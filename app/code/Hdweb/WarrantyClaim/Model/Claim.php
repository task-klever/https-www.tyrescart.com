<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hdweb\WarrantyClaim\Model;

use Hdweb\WarrantyClaim\Api\Data\ClaimInterface;
use Magento\Framework\Model\AbstractModel;

class Claim extends AbstractModel implements ClaimInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Hdweb\WarrantyClaim\Model\ResourceModel\Claim::class);
    }

    /**
     * @inheritDoc
     */
    public function getClaimId()
    {
        return $this->getData(self::CLAIM_ID);
    }

    /**
     * @inheritDoc
     */
    public function setClaimId($claimId)
    {
        return $this->setData(self::CLAIM_ID, $claimId);
    }

    /**
     * @inheritDoc
     */
    public function getWarrantyReference()
    {
        return $this->getData(self::WARRANTY_REFERENCE);
    }

    /**
     * @inheritDoc
     */
    public function setWarrantyReference($warrantyReference)
    {
        return $this->setData(self::WARRANTY_REFERENCE, $warrantyReference);
    }

    /**
     * @inheritDoc
     */
    public function getInvoiceNumber()
    {
        return $this->getData(self::INVOICE_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        return $this->setData(self::INVOICE_NUMBER, $invoiceNumber);
    }

    /**
     * @inheritDoc
     */
    public function getInvoiceDate()
    {
        return $this->getData(self::INVOICE_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setInvoiceDate($invoiceDate)
    {
        return $this->setData(self::INVOICE_DATE, $invoiceDate);
    }

    /**
     * @inheritDoc
     */
    public function getProductType()
    {
        return $this->getData(self::PRODUCT_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setProductType($productType)
    {
        return $this->setData(self::PRODUCT_TYPE, $productType);
    }

    /**
     * @inheritDoc
     */
    public function getInvoiceImage()
    {
        return $this->getData(self::INVOICE_IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function setInvoiceImage($invoiceImage)
    {
        return $this->setData(self::INVOICE_IMAGE, $invoiceImage);
    }

    /**
     * @inheritDoc
     */
    public function getVehiclePlate()
    {
        return $this->getData(self::VEHICLE_PLATE);
    }

    /**
     * @inheritDoc
     */
    public function setVehiclePlate($vehiclePlate)
    {
        return $this->setData(self::VEHICLE_PLATE, $vehiclePlate);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerName()
    {
        return $this->getData(self::CUSTOMER_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerName($customerName)
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }

    /**
     * @inheritDoc
     */
    public function getPhone()
    {
        return $this->getData(self::PHONE);
    }

    /**
     * @inheritDoc
     */
    public function setPhone($phone)
    {
        return $this->setData(self::PHONE, $phone);
    }

    /**
     * @inheritDoc
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritDoc
     */
    public function getProductImage1()
    {
        return $this->getData(self::PRODUCT_IMAGE1);
    }

    /**
     * @inheritDoc
     */
    public function setProductImage1($productImage1)
    {
        return $this->setData(self::PRODUCT_IMAGE1, $productImage1);
    }

    /**
     * @inheritDoc
     */
    public function getProductImage2()
    {
        return $this->getData(self::PRODUCT_IMAGE2);
    }

    /**
     * @inheritDoc
     */
    public function setProductImage2($productImage2)
    {
        return $this->setData(self::PRODUCT_IMAGE2, $productImage2);
    }

    /**
     * @inheritDoc
     */
    public function getProductImage3()
    {
        return $this->getData(self::PRODUCT_IMAGE3);
    }

    /**
     * @inheritDoc
     */
    public function setProductImage3($productImage3)
    {
        return $this->setData(self::PRODUCT_IMAGE3, $productImage3);
    }

    /**
     * @inheritDoc
     */
    public function getComment()
    {
        return $this->getData(self::COMMENT);
    }

    /**
     * @inheritDoc
     */
    public function setComment($comment)
    {
        return $this->setData(self::COMMENT, $comment);
    }

    /**
     * @inheritDoc
     */
    public function getCurrentMillage()
    {
        return $this->getData(self::CURRENT_MILLAGE);
    }

    /**
     * @inheritDoc
     */
    public function setCurrentMillage($currentMillage)
    {
        return $this->setData(self::CURRENT_MILLAGE, $currentMillage);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}

