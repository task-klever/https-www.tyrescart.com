<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hdweb\WarrantyClaim\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ClaimRepositoryInterface
{

    /**
     * Save Claim
     * @param \Hdweb\WarrantyClaim\Api\Data\ClaimInterface $claim
     * @return \Hdweb\WarrantyClaim\Api\Data\ClaimInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Hdweb\WarrantyClaim\Api\Data\ClaimInterface $claim
    );

    /**
     * Retrieve Claim
     * @param string $claimId
     * @return \Hdweb\WarrantyClaim\Api\Data\ClaimInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($claimId);

    /**
     * Retrieve Claim matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Hdweb\WarrantyClaim\Api\Data\ClaimSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Claim
     * @param \Hdweb\WarrantyClaim\Api\Data\ClaimInterface $claim
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Hdweb\WarrantyClaim\Api\Data\ClaimInterface $claim
    );

    /**
     * Delete Claim by ID
     * @param string $claimId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($claimId);
}

