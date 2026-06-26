<?php
namespace Hdweb\WarrantyClaim\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Hdweb\WarrantyClaim\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;
use Magento\Backend\Model\Auth\Session as AdminSession;

class Comment extends Template
{
    protected $commentCollectionFactory;
    protected $adminSession;

    public function __construct(
        Template\Context $context,
        CommentCollectionFactory $commentCollectionFactory,
        AdminSession $adminSession,
        array $data = []
    ) {
        $this->commentCollectionFactory = $commentCollectionFactory;
        $this->adminSession = $adminSession;
        parent::__construct($context, $data);
    }

   

    public function getComments()
    {
        $claimId = $this->getRequest()->getParam('claim_id');
        return $this->commentCollectionFactory->create()
            ->addFieldToFilter('warranty_id', $claimId)
            ->setOrder('created_at', 'DESC');
    }

    public function getAdminUsername($adminId)
    {
        try {
            return $this->adminSession->getUser()->load($adminId)->getUsername();
        } catch (\Exception $e) {
            return __('Unknown');
        }
    }
}
