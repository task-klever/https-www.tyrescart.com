<?php

namespace Klever\BannerSlider\Controller\Offer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Theme\Block\Html\Title as HtmlTitle;
use Mageplaza\BannerSlider\Model\BannerFactory;
use Magento\Framework\Controller\Result\ForwardFactory;

class View extends Action
{
    private PageFactory $resultPageFactory;
    private BannerFactory $bannerFactory;
    private ForwardFactory $forwardFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        BannerFactory $bannerFactory,
        ForwardFactory $forwardFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->bannerFactory = $bannerFactory;
        $this->forwardFactory = $forwardFactory;
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $bannerId = (int)$this->getRequest()->getParam('banner_id');
        if (!$bannerId) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        $banner = $this->bannerFactory->create()->load($bannerId);
        if (!$banner->getId() || !$banner->getStatus()) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        $resultPage = $this->resultPageFactory->create();

        $bannerName = $banner->getName();
        $metaTitle = $banner->getData('meta_title');
        $metaDescription = $banner->getData('meta_description');

        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle && $pageMainTitle instanceof HtmlTitle) {
            $pageMainTitle->setPageTitle($bannerName);
        }

        $resultPage->getConfig()->getTitle()->set(__($metaTitle ?: $bannerName . ' - Special Offers'));
        $resultPage->getConfig()->setDescription(
            __($metaDescription ?: __('%1 - Special Offers at TyresCart', $bannerName))
        );

        return $resultPage;
    }
}
