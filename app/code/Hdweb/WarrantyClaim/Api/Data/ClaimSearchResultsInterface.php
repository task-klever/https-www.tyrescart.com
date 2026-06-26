<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hdweb\WarrantyClaim\Api\Data;

interface ClaimSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Claim list.
     * @return \Hdweb\WarrantyClaim\Api\Data\ClaimInterface[]
     */
    public function getItems();

    /**
     * Set warranty_reference list.
     * @param \Hdweb\WarrantyClaim\Api\Data\ClaimInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

