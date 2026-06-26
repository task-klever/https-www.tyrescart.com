<?php
namespace Hdweb\Rfc\Model\ResourceModel;

class Rfc extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rfc', 'rfc_id');
    }
}
?>