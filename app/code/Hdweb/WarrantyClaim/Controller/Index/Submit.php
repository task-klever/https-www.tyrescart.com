<?php
namespace Hdweb\WarrantyClaim\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Hdweb\WarrantyClaim\Model\ClaimFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\DataObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;


use Hdweb\WarrantyClaim\Model\ResourceModel\Claim\CollectionFactory;

class Submit extends Action
{
    protected $uploaderFactory;
    protected $adapterFactory;
    protected $filesystem;
    protected $claimFactory;
    protected $sessionManager;
    protected $dateTime;

    protected $transportBuilder;
    protected $inlineTranslation;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        ClaimFactory $claimFactory,
        SessionManagerInterface $sessionManager,
        DateTime $dateTime,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->claimFactory = $claimFactory;
        $this->sessionManager = $sessionManager;
        $this->dateTime = $dateTime;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $request = $this->getRequest();
            $postData = $request->getPostValue();
            
            // Generate warranty reference number
            $today = $this->dateTime->gmtDate('dm');
            $lastClaim = $this->claimFactory->create()->getCollection()
                ->setOrder('claim_id', 'DESC')
                ->getFirstItem();
            $nextId = $lastClaim->getId() ? $lastClaim->getId() + 1 : 1;
            $warrantyReference = 'TEWC-' . $today . $nextId;
            
            // Initialize file paths as empty
            $invoiceImage = '';
            $productImages = [1 => '', 2 => '', 3 => ''];
            
            // Upload invoice image if provided
            if (!empty($_FILES['invoice_image']['name'])) {
                $uploader = $this->uploaderFactory->create(['fileId' => 'invoice_image']);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png' , 'pdf']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('warranty_claims/invoice');
                $result = $uploader->save($destinationPath);
                $invoiceImage = 'warranty_claims/invoice' . $result['file'];
            }
            
            // Upload product images if provided
            for ($i = 1; $i <= 3; $i++) {
                if (!empty($_FILES['product_image' . $i]['name'])) {
                    $uploader = $this->uploaderFactory->create(['fileId' => 'product_image' . $i]);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);
                    $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                    $destinationPath = $mediaDirectory->getAbsolutePath('warranty_claims/product');
                    $result = $uploader->save($destinationPath);
                    $productImages[$i] = 'warranty_claims/product' . $result['file'];
                }
            }
            
            // Save claim data
            $claim = $this->claimFactory->create();
            $claim->setData([
                'warranty_reference' => $warrantyReference,
                'invoice_number' => $postData['invoice_number'],
                'invoice_date' => $postData['invoice_date'],
                'product_type' => $postData['product_type'],
                'invoice_image' => $invoiceImage,
                'vehicle_plate' => $postData['vehicle_plate'],
                'customer_name' => $postData['customer_name'],
                'phone' => $postData['phone'],
                'email' => $postData['email'],
                'product_image1' => $productImages[1], // Assuming product_image1 is required
                'product_image2' => $productImages[2],
                'product_image3' => $productImages[3],
                'comment' => $postData['comment'] ?? '',
                'current_millage' => isset($postData['current_millage']) ? (int)$postData['current_millage'] : null,
                'created_at' => $this->dateTime->gmtDate(),
                'status' => 'pending'
            ]);
            $claim->save();
            
            $this->sendEmail($postData, $warrantyReference, $invoiceImage);
            
            // Set success message and warranty reference in session
            $this->sessionManager->setWarrantyReference($warrantyReference);
            $this->messageManager->addSuccessMessage(
                __('Your warranty claim has been submitted successfully. Your reference number is: %1', $warrantyReference)
            );
            
            // Redirect to success page
            $this->_redirect('warrantyclaim');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while submitting your warranty claim: %1', $e->getMessage())
            );
            $this->_redirect('warrantyclaim');
        }
    }


    public function sendEmail($postData, $warrantyReference , $invoiceImage)
    {
        $templateOptions = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
        ];

        $templateVars = [
            'warranty_reference'=> $warrantyReference,
            'invoice_number' => $postData['invoice_number'],
            'invoice_date' => $postData['invoice_date'],
            'product_type' => $postData['product_type'],
            'invoice_image' => $invoiceImage,
            'vehicle_plate' => $postData['vehicle_plate'],
            'customer_name' => $postData['customer_name'],
            'phone' => $postData['phone'],
            'email' => $postData['email'],
        
            'comment' => $postData['comment'],
            'current_millage' => $postData['current_millage'],

            
        ];

        $email = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
        $name  = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
        $from = array('email' => $email, 'name' => $name);
        $contactSendEmailsTo = $this->scopeConfig->getValue('contact/email/recipient_email', ScopeInterface::SCOPE_STORE);
        if ($contactSendEmailsTo && !empty($contactSendEmailsTo)) {
            $to = [$contactSendEmailsTo, $postData['email']];
        } else {
            $to = ['nirav.hdit@gmail.com', $postData['email']];
        }


        

       // $emailTemplate = $this->scopeConfig->getValue('hdwebemails/email_templates/send_enquiry', ScopeInterface::SCOPE_STORE);

        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier(19) // Set your email template identifier
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