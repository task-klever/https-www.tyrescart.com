<?php

namespace Klever\BannerSlider\Block;

use Magento\Framework\View\Element\Template;
use Mageplaza\BannerSlider\Model\BannerFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\UrlInterface;

class OfferView extends Template
{
    private BannerFactory $bannerFactory;
    private FilterProvider $filterProvider;

    public function __construct(
        Template\Context $context,
        BannerFactory $bannerFactory,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->bannerFactory = $bannerFactory;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    public function getBanner()
    {
        $bannerId = (int)$this->getRequest()->getParam('banner_id');
        if (!$bannerId) {
            return null;
        }
        $banner = $this->bannerFactory->create()->load($bannerId);
        return $banner->getId() ? $banner : null;
    }

    public function getBannerImageUrl(?string $image): string
    {
        if (!$image) {
            return '';
        }
        try {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            return $mediaUrl . 'mageplaza/bannerslider/banner/image/' . $image;
        } catch (\Exception $e) {
            return '';
        }
    }

    public function filterOutputHtml(string $string): string
    {
        try {
            return $this->filterProvider->getPageFilter()->filter($string);
        } catch (\Exception $e) {
            return $string;
        }
    }

    public function getSpecialOffersUrl(): string
    {
        return $this->getBaseUrl() . 'special-offers';
    }
}
