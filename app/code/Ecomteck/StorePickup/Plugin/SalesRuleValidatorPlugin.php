<?php
namespace Ecomteck\StorePickup\Plugin;

use Magento\SalesRule\Model\Validator;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;

class SalesRuleValidatorPlugin
{
    /**
     * Around plugin for the process method in Magento\SalesRule\Model\Validator
     *
     * @param Validator $subject
     * @param callable $proceed
     * @param AbstractItem $item
     * @param Rule $rule
     * @return Validator
     */
    public function aroundProcess(Validator $subject, callable $proceed, AbstractItem $item, Rule $rule)
    {
        $ruleId = $rule->getId();
        if($ruleId == 12){
            $quote = $item->getQuote();
            if ($quote->getPickupStore() != 128) {
                return $subject;
            }
        }
        
        return $proceed($item, $rule);
    }
}