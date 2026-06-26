<?php

namespace Hdweb\Rfc\Model;

use Magento\Framework\Model\AbstractModel;

class Rfc extends AbstractModel
{
	protected function _construct()
	{
		$this->_init('Hdweb\Rfc\Model\ResourceModel\Rfc');
	}
}