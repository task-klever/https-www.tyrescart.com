<?php

namespace Klever\Faq\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mageprince\Faq\Model\ResourceModel\FaqGroup\CollectionFactory as FaqGroupCollectionFactory;
use Mageprince\Faq\Model\ResourceModel\Faq\CollectionFactory as FaqCollectionFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageprince\Faq\Model\Config\DefaultConfig;

class AllFaqGroups implements ResolverInterface
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
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        $groupCollection = $this->groupCollectionFactory->create();
        $groupCollection->addFieldToFilter('status', 1);
        $groupCollection->addFieldToFilter('storeview', [['eq' => 0], ['finset' => $storeId]]);
        $groupCollection->setOrder('sortorder', 'ASC');

        $result = [];
        foreach ($groupCollection as $group) {
            $groupData = $group->getData();
            if (!empty($groupData['icon'])) {
                $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                $groupData['icon'] = $mediaUrl . DefaultConfig::ICON_TMP_PATH . $groupData['icon'];
            }

            // Load FAQs for this group
            $faqCollection = $this->faqCollectionFactory->create();
            $faqCollection->addFieldToFilter('status', 1);
            $faqCollection->addFieldToFilter('group', [['null' => true], ['finset' => $group->getId()]]);
            $faqCollection->addFieldToFilter('storeview', [['eq' => 0], ['finset' => $storeId]]);
            $faqCollection->setOrder('sortorder', 'ASC');

            $groupData['faqs'] = $faqCollection->getData();
            $result[] = $groupData;
        }

        return $result;
    }
}
