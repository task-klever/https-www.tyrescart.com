<?php

namespace Klever\BannerSlider\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Url;
use Mageplaza\BannerSlider\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;

class Router implements RouterInterface
{
    private ActionFactory $actionFactory;
    private BannerCollectionFactory $bannerCollectionFactory;

    public function __construct(
        ActionFactory $actionFactory,
        BannerCollectionFactory $bannerCollectionFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
    }

    /**
     * Match URLs: /special-offers/{banner-slug}
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        $identifier = trim($request->getPathInfo(), '/');

        if (strpos($identifier, 'special-offers/') !== 0) {
            return null;
        }

        $slug = substr($identifier, strlen('special-offers/'));
        if (empty($slug) || strpos($slug, '/') !== false) {
            return null;
        }

        $collection = $this->bannerCollectionFactory->create();
        $collection->addFieldToFilter('url_key', $slug);
        $collection->addFieldToFilter('status', 1);
        $banner = $collection->getFirstItem();

        if (!$banner->getId()) {
            return null;
        }

        $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
        $request->setPathInfo('/kleverbanner/offer/view');
        $request->setModuleName('kleverbanner');
        $request->setControllerName('offer');
        $request->setActionName('view');
        $request->setParam('banner_id', $banner->getId());
        $request->setDispatched(true);

        return $this->actionFactory->create(Forward::class, ['request' => $request]);
    }
}
