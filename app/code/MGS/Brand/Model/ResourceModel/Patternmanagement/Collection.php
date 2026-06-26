<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace MGS\Brand\Model\ResourceModel\Patternmanagement;

/**
 * Class Collection
 * @package Mageplaza\Shopbybrand\Model\ResourceModel\ProductsPage
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
	 * Initialize resource collection
	 *
	 * @return void
	 */
	public function _construct()
	{
		$this->_init('MGS\Brand\Model\Patternmanagement', 'MGS\Brand\Model\ResourceModel\Patternmanagement');
	}

	/**
	 * @return $this
	 */
	public function addVisibleFilter()
	{
		$this->addFieldToFilter('status', 1);

		return $this;
	}
}
