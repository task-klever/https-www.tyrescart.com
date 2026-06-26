<?php 
namespace Hdweb\WarrantyClaim\Controller\Adminhtml\Comment;

use Hdweb\WarrantyClaim\Model\CommentFactory;
use Magento\Backend\App\Action\Context;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\DataObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Hdweb\WarrantyClaim\Model\ClaimFactory;
use Magento\Backend\Model\UrlInterface;


class Submitcomment extends \Magento\Backend\App\Action
{
    protected $commentFactory;
    protected $authSession;

    protected $transportBuilder;
    protected $inlineTranslation;
    protected $scopeConfig;
    protected $claimFactory;
    protected $urlBuilder;

    public function __construct(
        Context $context,
        CommentFactory $commentFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        ClaimFactory $claimFactory,
        UrlInterface $urlBuilder
    ) {
        $this->commentFactory = $commentFactory;
        $this->authSession = $authSession;

        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->claimFactory = $claimFactory;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $claimId = $data['claim_id'] ?? null;
        $key = $this->getRequest()->getParam('key');

      

        if ($data) {
            try {
                $comment = $this->commentFactory->create();
                $adminUser = $this->authSession->getUser();

                $comment->setData([
                    'warranty_id' => $data['claim_id'],
                    'comment' => $data['comment'],
                    'status' => $data['status'],
                    'is_notify_customer' => isset($data['is_notify_customer']) ? 1 : 0,
                    'created_by' => $adminUser ? $adminUser->getId() : null,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                $comment->save();

                //  Update status in claim table
                $claim = $this->claimFactory->create()->load($data['claim_id']);
                if ($claim->getId()) {
                    $claim->setStatus($data['status']);
                    $claim->save();
                }

                // Optionally, send mail here if is_notify_customer is 1
                if ($comment->getIsNotifyCustomer()) {

                    $this->sendEmail($data);

                }

                $this->messageManager->addSuccessMessage(__('Comment saved successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Unable to save comment.'));
            }
        }

        // ✅ Generate fresh secure URL
            $redirectUrl = $this->urlBuilder->getUrl(
                'hdweb_warrantyclaim/claim/edit',
                ['claim_id' => $data['claim_id']]
            );
            return $this->_redirect($redirectUrl);
    }


    public function sendEmail($data)
    {

        $claim = $this->claimFactory->create()->load($data['claim_id']);
        $customerEmail = $claim->getEmail();
        $customerName = $claim->getCustomerName();

        $templateOptions = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
        ];

         $statusMap = [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'rejected' => 'Rejected',
            'declined' => 'Declined',
            'approved' => 'Approved'
        ];

        $prettyStatus = $statusMap[$data['status']] ?? ucfirst(str_replace('_', ' ', $data['status']));

        $templateVars = [
            'warranty_reference' => $claim->getWarrantyReference(),
            'comment' => $data['comment'],
            'status' => $prettyStatus,
        ];

        $email = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
        $name  = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
        $from = array('email' => $email, 'name' => $name);

        $to = $customerEmail;

       // $emailTemplate = $this->scopeConfig->getValue('hdwebemails/email_templates/send_enquiry', ScopeInterface::SCOPE_STORE);

        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier(20) // Set your email template identifier
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($to)
            //->addCc($copy_to_cc)
            //->addBcc($copy_to_bcc)
            ->getTransport();

        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

}
