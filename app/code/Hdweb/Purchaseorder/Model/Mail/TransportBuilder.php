<?php
namespace Hdweb\Purchaseorder\Model\Mail;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @param Api\AttachmentInterface $attachment
     */
    // public function addAttachment($pdfString)
    // {
    //     $this->message->createAttachment(
    //         $pdfString,
    //         'application/pdf',
    //         \Zend_Mime::DISPOSITION_ATTACHMENT,
    //         \Zend_Mime::ENCODING_BASE64,
    //         'attatched.pdf'
    //     );
    //     return $this;
    // }
     public function addAttachment($pdfString,$filename)
    {
       $attachment = new \Zend\Mime\Part($pdfString);
            $attachment->type = \Zend_Mime::TYPE_OCTETSTREAM;
            $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
            $attachment->filename = $filename;
        return $attachment;
    }

        public function clearHeader($headerName)
    {
        if (isset($this->_headers[$headerName])){
            unset($this->_headers[$headerName]);
        }
        return $this;
    }
}
