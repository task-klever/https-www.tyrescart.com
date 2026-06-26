<?php

namespace Klever\Faq\Plugin;

use Mageprince\Faq\Block\Index\Index;
use Mageprince\Faq\Model\ResourceModel\FaqGroup\Collection as FaqGroupCollection;

class ExcludeFooterGroupsFromFaqPage
{
    /**
     * Exclude FAQ groups that have footer_slugs from the main FAQ page
     */
    public function afterGetFaqGroupCollection(Index $subject, FaqGroupCollection $result): FaqGroupCollection
    {
        $result->addFieldToFilter(
            'footer_slugs',
            [
                ['null' => true],
                ['eq' => '']
            ]
        );
        return $result;
    }
}
