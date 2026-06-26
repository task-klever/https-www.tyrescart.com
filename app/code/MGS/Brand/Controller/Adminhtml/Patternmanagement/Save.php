<?php

namespace MGS\Brand\Controller\Adminhtml\Patternmanagement;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filter\FilterManager;
use MGS\Brand\Helper\Data as BrandHelper;

class Save extends \Magento\Backend\App\Action
{
	public $categoryFactory;

	/**
	 * @type \Magento\Framework\Filter\FilterManager
	 */
	protected $_filter;
	
	/**
	 * @type \Magento\Framework\Filesystem
	 */
	protected $_fileSystem;
	
	protected $eavConfig;
	protected $_brandHelper;
	protected $_eavConfig;

	/**
	 * Save constructor.
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Mageplaza\Shopbybrand\Model\PatternmanagementFactory $categoryFactory
	 * @param \Magento\Framework\Filter\FilterManager $filter
	 */

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\Filesystem $fileSystem,
		\MGS\Brand\Model\PatternmanagementFactory $categoryFactory,
		\MGS\Brand\Helper\Data $brandHelper,
		\Magento\Eav\Model\Config $eavConfig,
		FilterManager $filter
	)
	{
		$this->categoryFactory = $categoryFactory;
		$this->_fileSystem        = $fileSystem;
		$this->_brandHelper       = $brandHelper;
		$this->_filter     = $filter;
		$this->_eavConfig = $eavConfig;

		parent::__construct($context);
	}

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 * @return void
	 */

	public function execute()
	{
		$catId = $this->getRequest()->getParam('patternmanagement_id');
		$data   = $this->getRequest()->getParams();
		
		
		if ($data) {
			$brand = $this->getBrandName($data['brand_id']);
			$pattern = $this->getPatternName($data['pattern_id']);
			
			$data['brand'] = $brand;
			$data['pattern'] = $pattern;
			$data['created_at'] = date('Y-m-d H:i:s');
			$data['updated_at'] = date('Y-m-d H:i:s');
			$urlKey = $this->generateUrlKey($data);
			$data['url_key'] = $urlKey;

			$this->prepareData($data);
			$result = ['success' => true];
			$this->_uploadImage($data, $result);
			$cat = $this->categoryFactory->create();
			if ($catId) {
				$cat->load($catId);
			}
			
			$errors = $this->validateData($data);
			if (sizeof($errors)) {
				foreach ($errors as $error) {
					$this->messageManager->addErrorMessage($error);
				}

				if ($catId) {
					$this->_redirect('*/*/edit', array('patternmanagement_id' => $catId));
				} else {
					$this->_redirect('*/*/new');
				}

				return;
			}

			$cat->setData($data);

			try {
				$cat->save();

				$this->messageManager->addSuccessMessage(__('The pattern has been saved successfully.'));

				$this->_objectManager->get('Magento\Backend\Model\Session')->setProductFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('patternmanagement_id' => $cat->getId()));

					return;
				}

				$this->_redirect('*/*/');

				return;
			} catch (\RuntimeException $e) {
				$this->messageManager->addErrorMessage($e->getMessage());
			} catch (\Exception $e) {
				$this->messageManager->addErrorMessage($e->getMessage());
				$this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the pattern.'));
			}

			$this->_redirect('*/*/edit', array('patternmanagement_id' => $this->getRequest()->getParam('patternmanagement_id')));

			return;
		}
		$this->_redirect('*/*/');
	}

	/**
	 * @param $data
	 * @return mixed
	 */

	private function prepareData(&$data)
	{
		$data['url_key'] = $this->formatUrlKey($data['url_key']);

		$this->_getSession()->setProductFormData($data);

		return $data;
	}

	/**
	 * Format URL key from name or defined key
	 *
	 * @param string $str
	 * @return string
	 */

	public function formatUrlKey($str)
	{
		return $this->_filter->translitUrl($str);
	}

	/**
	 * Validate input data
	 *
	 * @param array $data
	 * @return array
	 */

	public function validateData(array $data)
	{
		$errors = [];

		if (!isset($data['brand_id'])) {
			$errors[] = __('Please enter the pattern name.');
		}
		
		if (!isset($data['pattern_id'])) {
			$errors[] = __('Please enter the pattern name.');
		}
		/* if (!isset($data['url_key'])) {
			$errors[] = __('Please enter the pattern url key.');
		} */ else {
			$pages = $this->categoryFactory->create()->getCollection()
				->addFieldToFilter('url_key', $data['url_key'])
				->addFieldToFilter('store_id', $data['store_id']);
			if (sizeof($pages)) {
				if (!isset($data['patternmanagement_id'])) {
					$errors[] = __('The url key "%1" has been used.', $data['url_key']);
				} else {
					foreach ($pages as $page) {
						if ($page->getId() != $data['patternmanagement_id']) {
							$errors[] = __('The url key "%1" has been used.', $data['url_key']);
						}
					}
				}
			}
		}

		return $errors;
	}

	/**
	 * Get input data function
	 * @return array
	 */
	public function getData()
	{
		$data   = $this->getRequest()->getParams();
		return $data;
	}
	
	public function getBrandName($brandOptionId)
	{
		
		/*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $brandObj = $objectManager->get('MGS\Brand\Model\Brand');
        $brand = $brandObj->load($brandOptionId);
        return $brand->getName();*/
        
		//$attributeCode = "brand";
		$attributeCode = "brand";
		$attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
		$optionlabel =  $attribute->getSource()->getOptionText($brandOptionId);
		return $optionlabel;
	}
	
	public function getPatternName($patternOptionId)
	{
		$attributeCode = "pattern";
		$attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
		$optionlabel =  $attribute->getSource()->getOptionText($patternOptionId);
		return $optionlabel;
	}
	
	public function generateUrlKey($data)
	{
		$slug = '';
		if($data['url_key'] != ''){
			$slug = $data['url_key'];
		}else{
			$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['pattern'])));
		}
		return $slug;
	}
	
	/**
	 * @param $data
	 * @param $result
	 * @return $this
	 */
	protected function _uploadImage(&$data, &$result)
	{
		if (isset($data['image']) && isset($data['image']['delete']) && $data['image']['delete']) {
			$data['image'] = '';
		} else {
			try {
				$uploader = $this->_objectManager->create(
					'Magento\MediaStorage\Model\File\Uploader',
					['fileId' => 'image']
				);
				$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
				$uploader->setAllowRenameFiles(true);

				$image = $uploader->save(
					$this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(BrandHelper::PATTERN_MEDIA_PATH)
				);

				$data['image'] = BrandHelper::PATTERN_MEDIA_PATH . '/' . $image['file'];
			} catch (\Exception $e) {
				$data['image'] = isset($data['image']['value']) ? $data['image']['value'] : '';
				if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
					$result['success'] = false;
					$result['message'] = $e->getMessage();
				}
			}
		}

		return $this;
	}
}
