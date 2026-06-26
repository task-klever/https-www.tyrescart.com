<?php

namespace Hdweb\Purchaseorder\Model\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve status options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Inactive')],
            ['value' => 1, 'label' => __('Active')]
        ];
    }
}