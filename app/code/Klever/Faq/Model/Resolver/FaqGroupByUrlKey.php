<?php

namespace Klever\Faq\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mageprince\Faq\Model\ResourceModel\FaqGroup\CollectionFactory as FaqGroupCollectionFactory;
use Mageprince\Faq\Model\ResourceModel\Faq\CollectionFactory as FaqCollectionFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageprince\Faq\Model\Config\DefaultConfig;

class FaqGroupByUrlKey implements ResolverInterface
{
    private FaqGroupCollectionFactory $groupCollectionFactory;
    private FaqCollectionFactory $faqCollectionFactory;
    private StoreManagerInterface $storeManager;

    public function __construct(
        FaqGroupCollectionFactory $groupCollectionFactory,
        FaqCollectionFactory $faqCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->faqCollectionFactory = $faqCollectionFactory;
        $this->storeManager = $storeManager;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (empty($args['url_key'])) {
            throw new GraphQlInputException(__('url_key is required.'));
        }

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        $collection = $this->groupCollectionFactory->create();
        $collection->addFieldToFilter('url_key', $args['url_key']);
        $collection->addFieldToFilter('status', 1);
        $group = $collection->getFirstItem();

        if (!$group->getId()) {
            throw new GraphQlNoSuchEntityException(
                __('FAQ group with url_key "%1" not found.', $args['url_key'])
            );
        }

        $data = $group->getData();
        if (!empty($data['icon'])) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $data['icon'] = $mediaUrl . DefaultConfig::ICON_TMP_PATH . $data['icon'];
        }

        // Load FAQs for this group
        $faqCollection = $this->faqCollectionFactory->create();
        $faqCollection->addFieldToFilter('status', 1);
        $faqCollection->addFieldToFilter('group', [['null' => true], ['finset' => $group->getId()]]);
        $faqCollection->addFieldToFilter('storeview', [['eq' => 0], ['finset' => $storeId]]);
        $faqCollection->setOrder('sortorder', 'ASC');

        $data['faqs'] = $faqCollection->getData();

        return $data;
    }
}
