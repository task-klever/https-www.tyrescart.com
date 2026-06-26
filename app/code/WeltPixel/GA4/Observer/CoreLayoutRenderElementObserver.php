<?php
namespace WeltPixel\GA4\Observer;

use Magento\Framework\Event\ObserverInterface;

class CoreLayoutRenderElementObserver implements ObserverInterface
{
    /**
     * @var \WeltPixel\GA4\Helper\Data
     */
    protected $helper;

    /**
     * @var \WeltPixel\GA4\Helper\ServerSideTracking
     */
    protected $serverSideHelper;

    /**
     * @param \WeltPixel\GA4\Helper\Data $helper
     * @param \WeltPixel\GA4\Helper\ServerSideTracking $serverSideHelper
     */
    public function __construct(
        \WeltPixel\GA4\Helper\Data $helper,
        \WeltPixel\GA4\Helper\ServerSideTracking $serverSideHelper
    ) {
        $this->helper = $helper;
        $this->serverSideHelper = $serverSideHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isEnabled() && !$this->serverSideHelper->isServerSideTrakingEnabled()) {
            return $this;
        }

        $elementName = $observer->getData('element_name');

        if (strpos($elementName, 'weltpixel_gtmga4_head') === false) {
            return $this;
        }

        if (!$this->helper->isEnabled() && $this->serverSideHelper->isServerSideTrakingEnabled()) {
            $this->serverSideHelper->addCategoryPageInformation();
            $this->serverSideHelper->addSearchResultPageInformation();
            $this->serverSideHelper->addProductPageInformation();
            $this->serverSideHelper->addCartPageInformation();
            $this->serverSideHelper->addOrderInformation();

            return $this;
        }

        $transport = $observer->getData('transport');
        $html = $transport->getOutput();

        $scriptContent = $this->helper->getDataLayerScript();
        $loadDataLayerBeforeGtmContainer = $this->helper->loadDataLayerBeforeGtmContainer();
        if ($loadDataLayerBeforeGtmContainer) {
            $html = $scriptContent . PHP_EOL . $html;
        } else {
            $html .= PHP_EOL . $scriptContent;
        }

        $transport->setOutput($html);

        return $this;
    }
}
