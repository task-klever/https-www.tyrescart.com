<?php
/**
 * Ecomteck_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @copyright 2016 Ecomteck
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @author    Ecomteck
 */
namespace Ecomteck\StoreLocator\Controller\Adminhtml\Stores;

use Ecomteck\StoreLocator\Model\Stores;

class MassDisable extends MassAction
{
    /**
     * @var bool
     */
    public $status = false;

    /**
     * @param Stores $stores
     * @return $this
     */
    public function massAction(Stores $stores)
    {
        $stores->setStatus($this->status);
        $this->storesRepository->save($stores);
        return $this;
    }
}
