<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @author    Ecomteck <ecomteck@gmail.com>
 * @copyright 2017 Ecomteck
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Ecomteck\StoreLocator\Model\Stores;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ecomteck\StoreLocator\Api\Data\StoresInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Area;

/**
 * Store Contact Form model.
 *
 * @category Ecomteck
 * @package  Ecomteck_StoreLocator
 * @author   Ecomteck <ecomteck@gmail.com>
 */
class ContactForm
{
    const XML_PATH_EMAIL_TEMPLATE = 'ecomteck_storelocator/contact/email_template';
    const XML_PATH_EMAIL_SENDER = 'ecomteck_storelocator/contact/sender_email_identity';
    const XML_PATH_EMAIL_COPY_TO = 'ecomteck_storelocator/contact/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD = 'ecomteck_storelocator/contact/copy_method';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $dataObject;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ContactForm constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig       Scope Config
     * @param \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder  Transport Builder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation Inline Translation
     * @param StoresInterface                                  $store          Current Retailer
     * @param array                                              $data              Form Data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoresInterface $store,
        $data
    ) {
        $this->scopeConfig       = $scopeConfig;
        $this->transportBuilder  = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->store          = $store;
        $this->dataObject        = new \Magento\Framework\DataObject($data);
        $this->storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * Send contact form
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @throws \Exception
     * @throws \Zend_Validate_Exception
     */
    public function send()
    {
        $postObject = $this->dataObject;
        $this->inlineTranslation->suspend();

        try {
            $this->validate();
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport  = $this->transportBuilder
                ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
                ->setTemplateOptions(
                    [
                        'area'  => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope))
                ->addTo($this->store->getEmail())
                ->setReplyTo($this->dataObject->getData('email'));
            

            $copyTo = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_COPY_TO, $storeScope);
            $copyMethod = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_COPY_METHOD, $storeScope);
            if (!empty($copyTo) && $copyMethod == 'bcc') {
                $copyTo = explode(',',$copyTo);
                foreach ($copyTo as $email) {
                    $this->transportBuilder->addBcc($email);
                }
            }

            $transport = $this->transportBuilder->getTransport();

            $transport->sendMessage();
            
            if(!empty($copyTo) && $copyMethod == 'copy'){
                $copyTo = explode(',',$copyTo);
                
                foreach ($copyTo as $email) {
                    $this->transportBuilder
                    ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
                    ->setTemplateOptions(
                        [
                            'area'  => Area::AREA_FRONTEND,
                            'store' => $this->storeManager->getStore()->getId(),
                        ]
                    )
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope))
                    ->setReplyTo($this->dataObject->getData('email'));
                    $this->transportBuilder->addTo($email);
                    $transport = $this->transportBuilder->getTransport();
                    $transport->sendMessage();
                } 
            }
            
            $this->inlineTranslation->resume();
        } catch (\Exception $exception) {
            $this->inlineTranslation->resume();
            throw $exception;
        }
    }

    /**
     * Send contact form
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @throws \Exception
     * @throws \Zend_Validate_Exception
     */
    private function validate()
    {
        $post = $this->dataObject->getData();

        $error = false;

        if (!\Zend_Validate::is(trim($post['name']), 'NotEmpty')) {
            $error = __('Name cannot be empty');
        }
        if (!\Zend_Validate::is(trim($post['comment']), 'NotEmpty')) {
            $error = __('Contact form cannot be empty');
        }
        if (!\Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
            $error = __('Contact mail cannot be empty');
        }
        if (\Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
            $error = __('Unable to validate form');
        }

        if ((!$this->store->getId()) || (!$this->store->getEmail())) {
            $error = __('Unable to retrieve store informations');
        }

        if (false !== $error) {
            throw new \Exception($error);
        }
    }
}
