<?php

namespace Hdweb\Rfc\Model\ResourceModel\Rfc;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hdweb\Rfc\Model\Rfc', 'Hdweb\Rfc\Model\ResourceModel\Rfc');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>