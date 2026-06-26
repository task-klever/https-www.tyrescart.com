<?php
namespace Hdweb\Rfc\Block\Adminhtml\Manage\Edit\Tab;

use Magento\Framework\DataObject;

class Getdatetime extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
		$rfcName = $row->getRfcName();
		$collection = $this->_rfcFactory->create()->getCollection()
                    ->addFieldToFilter('rfc_name', $rfcName)
                    ->getLastItem();
        $rfcDatetime = $collection->getRfcDatetime();
        $strtotime = strtotime($rfcDatetime);
		$date = date('M j, Y',$strtotime);
        $time = date('h:i:s A',$strtotime);

		return $date." ".$time;
	}
}