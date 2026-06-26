<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Model\Config\Source;

use Magento\Cms\Model\Page;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\App\RequestInterface;

class CmsPages implements OptionSourceInterface
{
    /**
     * @param Page $cmsPage
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly Page $cmsPage,
        private readonly RequestInterface $request,
    ) {
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $storeId = (int) $this->request->getParam('store', 0);

        $collection = $this->cmsPage->getCollection()
            ->addFieldToFilter('is_active', 1);

        if ($storeId) {
            $collection->addStoreFilter($storeId);
        }

        foreach ($collection as $page) {
            $options[] = [
                'value' => $page->getId(),
                'label' => $page->getTitle() . ' (' . $page->getIdentifier() . ')',
            ];
        }

        return $options;
    }
}

