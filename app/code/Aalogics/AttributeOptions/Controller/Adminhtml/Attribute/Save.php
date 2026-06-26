<?php
namespace Aalogics\AttributeOptions\Controller\Adminhtml\Attribute;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
    * @var \Magento\Framework\View\Result\PageFactory
    */
    protected $resultPageFactory;
    protected $_eavSetupFactory;
    protected $_storeManager;
    protected $_attributeFactory;
    protected $messageManager;
    protected $resultFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
         parent::__construct($context);
         $this->resultPageFactory = $resultPageFactory;
         $this->_eavSetupFactory = $eavSetupFactory;
        $this->_storeManager = $storeManager;
        $this->_attributeFactory = $attributeFactory;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
    }

    public function execute()
    {
        $data = (array)$this->getRequest()->getPost();

        if(($data['attribute_code'] == '') || ($data['attribute_values'] == '')) {
            // return;
            $this->messageManager->addErrorMessage("Please add attribute_code/values to proceed");
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        // $attribute_code = 'style';
        // $attribute_array = ['0.5 Kg',"12.5' length",'12-18 Months','12" Yellow'];
        $attribute_code = $data['attribute_code'];
        $attribute_array = explode(',',$data['attribute_values']);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        // Get existing options of attribute
        $existingOptions = $this->getExistingOptions($objectManager, $attribute_code);

        // compare new options with existing options and get new options to insert
        $new_options = $this->compareOptions($attribute_array, $existingOptions);
        $storeArray = $this->getAllStores($objectManager);

        // add new options to attribute
        $addedValues = $this->addNewOptions($objectManager, $new_options, $storeArray, $attribute_code);

        $this->messageManager->addSuccessMessage("Added Values: " . implode(', ', $addedValues));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    function addNewOptions($object_Manager, $newOptions, $storeArray, $attribute_code) {
    
        $option = array();
        $eavConfig = $object_Manager->get('\Magento\Eav\Model\Config');
        $attribute = $eavConfig->getAttribute('catalog_product', $attribute_code);
        $option['attribute_id'] = $attribute->getAttributeId();
        $addedValues = array();
        
        foreach($newOptions as $key => $value){
            
            $value = '"'.$value.'"';
            $option['value'][$value][0] = str_replace('"','', $value);
            // $option['value'][$value][0]=$value;
            
            // foreach($storeArray as $storeKey => $store) {
            //     $option['value'][$value][$storeKey] = $value;
            //     // $option['value'][$value][$storeKey] = str_replace('"','', $value);
            // }   
            array_push($addedValues, $value);
        }
     
        try{
            $eavSetup = $this->_eavSetupFactory->create();
            $eavSetup->addAttributeOption($option);

            return $addedValues;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    function getExistingOptions($object_Manager, $attribute_code) {
    
        $eavConfig = $object_Manager->get('\Magento\Eav\Model\Config');
        $attribute = $eavConfig->getAttribute('catalog_product', $attribute_code);
        $options = $attribute->getSource()->getAllOptions();
     
        $existingOptions = array();

        foreach($options as $option) {
            $existingOptions[] = $option['label'];
        }
        
        return $existingOptions;
    }

    function compareOptions($optionsValues, $existingOptions) {
  
        $newOptions = array_diff($optionsValues, $existingOptions);
        return $newOptions;
    }
     
    function getAllStores($object_Manager) {
        $storeManager = $object_Manager->get('Magento\Store\Model\StoreManagerInterface');
        $stores = $storeManager->getStores();
        $storeArray[0] = "All Store Views";       
     
        foreach ($stores  as $store) {
            $storeArray[$store->getId()] = $store->getName();
        }
        return $storeArray;
    }
}
?>
