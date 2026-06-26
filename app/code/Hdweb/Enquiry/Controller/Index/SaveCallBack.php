<?php
namespace Hdweb\Enquiry\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Hdweb\Enquiry\Model\EnquiryFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Store\Model\StoreManagerInterface;

class SaveCallBack extends Action
{
    protected $enquiryFactory;
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $scopeConfig;
    protected $formKeyValidator;
    protected $dataPersistor;
    protected $storeManager;

    public function __construct(
        Context $context,
        EnquiryFactory $enquiryFactory,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        FormKeyValidator $formKeyValidator,
        DataPersistorInterface $dataPersistor,
         StoreManagerInterface $storeManager
    ) {
        $this->enquiryFactory = $enquiryFactory;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->formKeyValidator = $formKeyValidator;
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $request = $this->getRequest();

        if (!$this->formKeyValidator->validate($request)) {
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        if ($request->isPost()) {
            $postData = $request->getPostValue();

            try {
                // Save data in database
                $enquiry = $this->enquiryFactory->create();
                $enquiry->setName($postData['name'] ?? '');
                $enquiry->setNumber($postData['number'] ?? '');
                $enquiry->setFormType('Request Call Back Form'); // you can set any form type name
                $enquiry->setStatus(1);
                $enquiry->save();

                // Send email
                $this->sendEmail($postData);

                $this->messageManager->addSuccessMessage(__('Your form is submitted successfully! We will contact you soon.'));

                return $this->_redirect('/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong. Please try again.'));
                return $this->_redirect($this->_redirect->getRefererUrl());
            }
        }

        return $this->_redirect('/');
    }

    protected function sendEmail($postData)
    {
        try {
            $templateIdentifier = $this->scopeConfig->getValue(
                'hdwebemails/email_templates/send_enquiry',
                ScopeInterface::SCOPE_STORE
            );

            $email = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
            $name  = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
            $toEmail = $this->scopeConfig->getValue('contact/email/recipient_email', ScopeInterface::SCOPE_STORE)
            ?: 'vicky.hdit@gmail.com';

            $templateVars = [
                'name' => $postData['name'] ?? '',
                'number' => $postData['number'] ?? ''
            ];

            $templateOptions = [
                'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()
            ];

            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier(17)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom(['email' => $email, 'name' => $name])
                ->addTo($toEmail)
                ->getTransport();

            $transport->sendMessage();

            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            file_put_contents(BP . '/var/log/callback_email_error.log', $e->getMessage(), FILE_APPEND);
        }
    }
}
