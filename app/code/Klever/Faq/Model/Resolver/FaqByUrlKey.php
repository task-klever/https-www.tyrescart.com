<?php

namespace Klever\Faq\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mageprince\Faq\Model\ResourceModel\FaqGroup\CollectionFactory as FaqGroupCollectionFactory;
use Mageprince\Faq\Model\ResourceModel\Faq\CollectionFactory as FaqCollectionFactory;

class FaqByUrlKey implements ResolverInterface
{
    private FaqGroupCollectionFactory $groupCollectionFactory;
    private FaqCollectionFactory $faqCollectionFactory;

    public function __construct(
        FaqGroupCollectionFactory $groupCollectionFactory,
        FaqCollectionFactory $faqCollectionFactory
    ) {
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->faqCollectionFactory = $faqCollectionFactory;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (empty($args['group_url_key']) || empty($args['faq_url_key'])) {
            throw new GraphQlInputException(__('Both group_url_key and faq_url_key are required.'));
        }

        $groupCollection = $this->groupCollectionFactory->create();
        $groupCollection->addFieldToFilter('url_key', $args['group_url_key']);
        $groupCollection->addFieldToFilter('status', 1);
        $group = $groupCollection->getFirstItem();

        if (!$group->getId()) {
            throw new GraphQlNoSuchEntityException(
                __('FAQ group "%1" not found.', $args['group_url_key'])
            );
        }

        $faqCollection = $this->faqCollectionFactory->create();
        $faqCollection->addFieldToFilter('url_key', $args['faq_url_key']);
        $faqCollection->addFieldToFilter('status', 1);
        $faqCollection->addFieldToFilter('group', [['null' => true], ['finset' => $group->getId()]]);
        $faq = $faqCollection->getFirstItem();

        if (!$faq->getId()) {
            throw new GraphQlNoSuchEntityException(
                __('FAQ "%1" not found in group "%2".', $args['faq_url_key'], $args['group_url_key'])
            );
        }

        return $faq->getData();
    }
}
