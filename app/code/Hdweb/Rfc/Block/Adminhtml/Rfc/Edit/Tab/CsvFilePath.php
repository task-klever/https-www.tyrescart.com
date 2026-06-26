<?php
namespace Hdweb\Rfc\Block\Adminhtml\Rfc\Edit\Tab;

use Magento\Framework\DataObject;

class CsvFilePath extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
	protected $_rfcFactory;
	protected $storeManagerInterface;

	public function __construct(
		\Hdweb\Rfc\Model\RfcFactory $RfcFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManagerInterface
	) {
		$this->_rfcFactory = $RfcFactory;
		$this->storeManagerInterface = $storeManagerInterface;
	}

	public function render(DataObject $row)
	{
		$currentStore = $this->storeManagerInterface->getStore();
		$mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$csvPath = $mediaUrl.'rfcfiles/'.$row->getRfcManualPath();
		return '<a href="' . $csvPath . '">' . $row->getRfcManualPath() . '</a>';
	}
}