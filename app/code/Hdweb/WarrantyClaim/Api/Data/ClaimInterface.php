<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hdweb\WarrantyClaim\Api\Data;

interface ClaimInterface
{

    const PRODUCT_TYPE = 'product_type';
    const INVOICE_DATE = 'invoice_date';
    const INVOICE_NUMBER = 'invoice_number';
    const WARRANTY_REFERENCE = 'warranty_reference';
    const INVOICE_IMAGE = 'invoice_image';
    const PRODUCT_IMAGE2 = 'product_image2';
    const CLAIM_ID = 'claim_id';
    const EMAIL = 'email';
    const PRODUCT_IMAGE1 = 'product_image1';
    const CURRENT_MILLAGE = 'current_millage';
    const COMMENT = 'comment';
    const STATUS = 'status';
    const VEHICLE_PLATE = 'vehicle_plate';
    const PHONE = 'phone';
    const PRODUCT_IMAGE3 = 'product_image3';
    const CUSTOMER_NAME = 'customer_name';

    /**
     * Get claim_id
     * @return string|null
     */
    public function getClaimId();

    /**
     * Set claim_id
     * @param string $claimId
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setClaimId($claimId);

    /**
     * Get warranty_reference
     * @return string|null
     */
    public function getWarrantyReference();

    /**
     * Set warranty_reference
     * @param string $warrantyReference
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setWarrantyReference($warrantyReference);

    /**
     * Get invoice_number
     * @return string|null
     */
    public function getInvoiceNumber();

    /**
     * Set invoice_number
     * @param string $invoiceNumber
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setInvoiceNumber($invoiceNumber);

    /**
     * Get invoice_date
     * @return string|null
     */
    public function getInvoiceDate();

    /**
     * Set invoice_date
     * @param string $invoiceDate
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setInvoiceDate($invoiceDate);

    /**
     * Get product_type
     * @return string|null
     */
    public function getProductType();

    /**
     * Set product_type
     * @param string $productType
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setProductType($productType);

    /**
     * Get invoice_image
     * @return string|null
     */
    public function getInvoiceImage();

    /**
     * Set invoice_image
     * @param string $invoiceImage
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setInvoiceImage($invoiceImage);

    /**
     * Get vehicle_plate
     * @return string|null
     */
    public function getVehiclePlate();

    /**
     * Set vehicle_plate
     * @param string $vehiclePlate
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setVehiclePlate($vehiclePlate);

    /**
     * Get customer_name
     * @return string|null
     */
    public function getCustomerName();

    /**
     * Set customer_name
     * @param string $customerName
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setCustomerName($customerName);

    /**
     * Get phone
     * @return string|null
     */
    public function getPhone();

    /**
     * Set phone
     * @param string $phone
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setPhone($phone);

    /**
     * Get email
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     * @param string $email
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setEmail($email);

    /**
     * Get product_image1
     * @return string|null
     */
    public function getProductImage1();

    /**
     * Set product_image1
     * @param string $productImage1
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setProductImage1($productImage1);

    /**
     * Get product_image2
     * @return string|null
     */
    public function getProductImage2();

    /**
     * Set product_image2
     * @param string $productImage2
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setProductImage2($productImage2);

    /**
     * Get product_image3
     * @return string|null
     */
    public function getProductImage3();

    /**
     * Set product_image3
     * @param string $productImage3
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setProductImage3($productImage3);

    /**
     * Get comment
     * @return string|null
     */
    public function getComment();

    /**
     * Set comment
     * @param string $comment
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setComment($comment);

    /**
     * Get current_millage
     * @return string|null
     */
    public function getCurrentMillage();

    /**
     * Set current_millage
     * @param string $currentMillage
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setCurrentMillage($currentMillage);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Hdweb\WarrantyClaim\Claim\Api\Data\ClaimInterface
     */
    public function setStatus($status);
}

