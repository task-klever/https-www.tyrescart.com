<?php
/**
 * Custom TransportBuilder that properly handles PDF attachments.
 *
 * Magento's MimeMessage drops non-TextPart parts, so attachments added via
 * Hdweb TransportBuilder::addAttachment() are lost in the MIME chain.
 *
 * This override saves attachment data, lets parent build the transport normally,
 * then injects attachments into the Symfony Message inside the EmailMessage
 * before returning the transport.
 */
declare(strict_types=1);

namespace Klever\OrderActions\Model\Mail\Template;

use Hdweb\Core\Model\Mail\Template\TransportBuilder as HdwebTransportBuilder;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address as SymfonyAddress;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Symfony\Component\Mime\Part\TextPart;

class TransportBuilder extends HdwebTransportBuilder
{
    /**
     * @var array Saved attachment data (content, filename, type)
     */
    private $savedAttachments = [];

    /**
     * Override addAttachment to save attachment data locally.
     *
     * @param string|null $content
     * @param string|null $fileName
     * @param string|null $fileType
     * @return $this
     */
    public function addAttachment(?string $content, ?string $fileName, ?string $fileType)
    {
        $this->savedAttachments[] = [
            'content' => $content,
            'filename' => $fileName,
            'type' => $fileType,
        ];
        /* Still call parent so it doesn't break the chain */
        return parent::addAttachment($content, $fileName, $fileType);
    }

    /**
     * Override getTransport — after parent builds the transport, inject
     * attachments into the Symfony Message inside the EmailMessage.
     *
     * @return \Magento\Framework\Mail\TransportInterface
     */
    public function getTransport()
    {
        $attachments = $this->savedAttachments;
        $this->savedAttachments = [];

        /* Let parent build transport normally (EmailMessage + Transport) */
        $transport = parent::getTransport();

        if (empty($attachments)) {
            return $transport;
        }

        /* Get the EmailMessage from the transport */
        $emailMessage = $transport->getMessage();

        /* Get the inner Symfony Message and rebuild as Email with attachments */
        $symfonyMessage = $emailMessage->getSymfonyMessage();

        /* Build a new Symfony Email with the same headers + body + attachments */
        $symfonyEmail = new Email();

        /* Copy From */
        $fromHeader = $symfonyMessage->getHeaders()->get('From');
        if ($fromHeader) {
            foreach ($fromHeader->getAddresses() as $addr) {
                $symfonyEmail->from($addr);
            }
        }

        /* Copy To */
        $toHeader = $symfonyMessage->getHeaders()->get('To');
        if ($toHeader) {
            foreach ($toHeader->getAddresses() as $addr) {
                $symfonyEmail->addTo($addr);
            }
        }

        /* Copy Cc */
        $ccHeader = $symfonyMessage->getHeaders()->get('Cc');
        if ($ccHeader) {
            foreach ($ccHeader->getAddresses() as $addr) {
                $symfonyEmail->addCc($addr);
            }
        }

        /* Copy Bcc */
        $bccHeader = $symfonyMessage->getHeaders()->get('Bcc');
        if ($bccHeader) {
            foreach ($bccHeader->getAddresses() as $addr) {
                $symfonyEmail->addBcc($addr);
            }
        }

        /* Copy Subject */
        $subjectHeader = $symfonyMessage->getHeaders()->get('Subject');
        if ($subjectHeader) {
            $symfonyEmail->subject($subjectHeader->getBodyAsString());
        }

        /* Copy body */
        $body = $symfonyMessage->getBody();
        if ($body instanceof TextPart) {
            $symfonyEmail->html($body->getBody());
        }

        /* Add attachments */
        foreach ($attachments as $att) {
            $symfonyEmail->attach($att['content'], $att['filename'], $att['type']);
        }

        /* Replace the Symfony message inside EmailMessage using reflection */
        $ref = new \ReflectionClass($emailMessage);
        $prop = $ref->getProperty('symfonyMessage');
        $prop->setAccessible(true);
        $prop->setValue($emailMessage, $symfonyEmail);

        return $transport;
    }
}
