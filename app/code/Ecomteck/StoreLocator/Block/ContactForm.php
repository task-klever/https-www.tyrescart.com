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
namespace Ecomteck\StoreLocator\Block;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Ecomteck\StoreLocator\Model\Config as StoreLocatorConfig;
/**
 * Store Locator Contact Form
 *
 * @category Ecomteck
 * @package  Ecomteck_StoreLocator
 * @author   Ecomteck <ecomteck@gmail.com>
 */
class ContactForm extends StoreDetail
{
    /**
     * @var \Ecomteck\StoreLocator\Helper\Data
     */
    private $storeLocatorHelper;

    /**
     * @var array
     */
    private $postData = null;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * ContactForm constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context                Application Context
     * @param \Magento\Framework\Registry                      $coreRegistry           Core Registry
     * @param \Ecomteck\StoreLocator\Helper\Data                  $storeLocatorHelper     Store Locator Helper
     * @param DataPersistorInterface                           $dataPersistorInterface Data Persistor Interface
     * @param array                                            $data                   Block data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        StoreLocatorConfig $storelocatorConfig,
        DataPersistorInterface $dataPersistorInterface,
        array $data
    ) {
        $this->dataPersistor      = $dataPersistorInterface; 
        parent::__construct($storelocatorConfig,$coreRegistry,$context, $data);
        $this->_isScopePrivate    = true;
    }

    /**
     * Return form action url
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('storelocator/view/contactPost', ['id'=>$this->getStore()->getId(),'_secure' => true]);
    }

    /**
     * Get value from POST by key
     *
     * @param string $key The key
     *
     * @return string
     */
    public function getPostValue($key)
    {
        if (null === $this->postData) {
            $this->postData = (array) $this->dataPersistor->get('contact_store');
            $this->dataPersistor->clear('contact_store');
        }

        if (isset($this->postData[$key])) {
            return (string) $this->postData[$key];
        }

        return '';
    }
}
