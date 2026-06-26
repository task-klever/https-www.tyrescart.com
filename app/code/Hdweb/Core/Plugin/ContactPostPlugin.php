<?php

namespace Hdweb\Core\Plugin;

use Magento\Contact\Controller\Index\Post as ContactPostController;
use Magento\Framework\App\RequestInterface;
use Hdweb\Enquiry\Model\EnquiryFactory;

class ContactPostPlugin
{
    protected $request;
    protected $_enquiry;

    public function __construct(
        RequestInterface $request,
        EnquiryFactory $enquiry
    ) {
        $this->request = $request;
        $this->_enquiry = $enquiry;
    }

    public function aroundExecute(ContactPostController $subject, callable $proceed)
    {
        try {
            // Get post data from the contact form
            $postData = $this->request->getPostValue();
            $enquiry = $this->_enquiry->create();
            $enquiry->setName($postData['name']);
            $enquiry->setEmail($postData['email']);
            $enquiry->setNumber($postData['telephone']);
            $enquiry->setMessage($postData['comment']);
            $enquiry->setFormType('Contact Us');
            $enquiry->save();

            // Your custom logic here to manipulate or use the post data

            // Execute the original method
            $result = $proceed();

            // Your custom logic after the original method execution
            // For example, you can modify the result or perform additional actions

            return $result;
        } catch (\Exception $e) {
            // Handle exceptions if needed
            throw $e;
        }
    }
}
