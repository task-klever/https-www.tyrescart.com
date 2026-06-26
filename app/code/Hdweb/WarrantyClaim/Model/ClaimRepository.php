<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hdweb\WarrantyClaim\Model;

use Hdweb\WarrantyClaim\Api\ClaimRepositoryInterface;
use Hdweb\WarrantyClaim\Api\Data\ClaimInterface;
use Hdweb\WarrantyClaim\Api\Data\ClaimInterfaceFactory;
use Hdweb\WarrantyClaim\Api\Data\ClaimSearchResultsInterfaceFactory;
use Hdweb\WarrantyClaim\Model\ResourceModel\Claim as ResourceClaim;
use Hdweb\WarrantyClaim\Model\ResourceModel\Claim\CollectionFactory as ClaimCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ClaimRepository implements ClaimRepositoryInterface
{

    /**
     * @var ClaimCollectionFactory
     */
    protected $claimCollectionFactory;

    /**
     * @var ClaimInterfaceFactory
     */
    protected $claimFactory;

    /**
     * @var Claim
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceClaim
     */
    protected $resource;


    /**
     * @param ResourceClaim $resource
     * @param ClaimInterfaceFactory $claimFactory
     * @param ClaimCollectionFactory $claimCollectionFactory
     * @param ClaimSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceClaim $resource,
        ClaimInterfaceFactory $claimFactory,
        ClaimCollectionFactory $claimCollectionFactory,
        ClaimSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->claimFactory = $claimFactory;
        $this->claimCollectionFactory = $claimCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(ClaimInterface $claim)
    {
        try {
            $this->resource->save($claim);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the claim: %1',
                $exception->getMessage()
            ));
        }
        return $claim;
    }

    /**
     * @inheritDoc
     */
    public function get($claimId)
    {
        $claim = $this->claimFactory->create();
        $this->resource->load($claim, $claimId);
        if (!$claim->getId()) {
            throw new NoSuchEntityException(__('Claim with id "%1" does not exist.', $claimId));
        }
        return $claim;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->claimCollectionFactory->create();
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(ClaimInterface $claim)
    {
        try {
            $claimModel = $this->claimFactory->create();
            $this->resource->load($claimModel, $claim->getClaimId());
            $this->resource->delete($claimModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Claim: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($claimId)
    {
        return $this->delete($this->get($claimId));
    }
}

