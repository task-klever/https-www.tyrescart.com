<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Controller\Adminhtml\Manage;

use BitExpert\ForceCustomerLogin\Model\WhitelistDefaultInstaller;
use BitExpert\ForceCustomerLogin\Model\ResourceModel\WhitelistEntry as WhitelistEntryResource;
use BitExpert\ForceCustomerLogin\Model\ResourceModel\WhitelistEntry\CollectionFactory as WhitelistEntryCollectionFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class RestoreDefault
 *
 * @package BitExpert\ForceCustomerLogin\Controller\Adminhtml\Manage
 * @codingStandardsIgnoreFile
 */
class RestoreDefault extends \Magento\Backend\App\Action
{
    private RedirectFactory $redirectFactory;
    private WhitelistDefaultInstaller $whitelistDefaultInstaller;
    private WhitelistEntryResource $whitelistEntryResource;
    private WhitelistEntryCollectionFactory $whitelistEntryCollectionFactory;

    /**
     * RestoreDefault constructor.
     */
    public function __construct(
        Context $context,
        WhitelistDefaultInstaller $whitelistDefaultInstaller,
        WhitelistEntryResource $whitelistEntryResource,
        WhitelistEntryCollectionFactory $whitelistEntryCollectionFactory
    ) {
        parent::__construct($context);
        $this->redirectFactory = $context->getResultRedirectFactory();
        $this->whitelistDefaultInstaller = $whitelistDefaultInstaller;
        $this->whitelistEntryResource = $whitelistEntryResource;
        $this->whitelistEntryCollectionFactory = $whitelistEntryCollectionFactory;
    }

    /**
     * Restore whitelist defaults action.
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $result = $this->redirectFactory->create();
        $result->setPath('ForceCustomerLogin/Manage/index');

        try {
            $resource = $this->whitelistEntryResource;
            $collection = $this->whitelistEntryCollectionFactory->create();

            $resource->beginTransaction();
            $collection->walk(function ($entry) {
                $entry->delete();
            });
            $this->whitelistDefaultInstaller->install();
            $resource->commit();
        } catch (\Exception $e) {
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR);
            $this->messageManager->addErrorMessage(
                __("Could not restore default whitelist!")
            );
            return $result;
        }

        $result->setHttpResponseCode(200);
        $this->messageManager->addSuccessMessage(
            __("Successfully restored whitelist defaults.")
        );

        return $result;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BitExpert_ForceCustomerLogin::bitexpert_force_customer_login_manage');
    }
}
