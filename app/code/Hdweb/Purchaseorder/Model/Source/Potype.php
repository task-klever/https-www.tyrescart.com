<?php

namespace Hdweb\Purchaseorder\Model\Source;

class Potype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve Potype options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => '', 'label' => ''],
			['value' => 'fpo', 'label' => __('FPO')],
            ['value' => 'mpo', 'label' => __('MPO')],
            ['value' => 'ppo', 'label' => __('PPO')]
        ];
    }
}