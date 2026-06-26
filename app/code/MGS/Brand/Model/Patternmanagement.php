<?php

namespace MGS\Brand\Model;

use Magento\Framework\Model\AbstractModel;

class Patternmanagement extends AbstractModel
{
	protected function _construct()
	{
		$this->_init('MGS\Brand\Model\ResourceModel\Patternmanagement');
	}
}