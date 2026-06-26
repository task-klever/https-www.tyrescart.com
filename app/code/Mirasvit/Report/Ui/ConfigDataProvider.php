<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report
 * @version   1.4.38
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Ui;

use Magento\Backend\Block\Template;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Core\Service\CspService;
use Mirasvit\Report\Api\Service\DateServiceInterface;

class ConfigDataProvider extends Template
{
    private $dateService;

    private $serializer;

    private $storeManager;

    public function __construct(
        DateServiceInterface $dateService,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        StoreManagerInterface $storeManager,
        Template\Context $context
    ) {
        $this->dateService  = $dateService;
        $this->serializer   = $serializer;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getConfigData()
    {
        $result = [
            'dateRange' => [],
        ];

        foreach ($this->dateService->getIntervals() as $identifier => $label) {
            $range = $this->dateService->getInterval($identifier);

            $result['dateRange'][$identifier] = [
                'label' => $label,
                'from'  => $range->getFrom()->toString('Y-MM-ddTHH:mm:ss'),
                'to'    => $range->getTo()->toString('Y-MM-ddTHH:mm:ss'),
            ];
        }

        $result['shareLink'] = $this->getBaseShareUrl();

        return $result;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $json   = $this->serializer->serialize($this->getConfigData());
        $nonce  = CspService::getNonce();
        $script = $nonce ? '<script nonce="' . $this->escapeHtml($nonce) . '">' : '<script>';

        return $script . "var configDataProvider = $json</script>";
    }

    public function getBaseShareUrl(): string
    {
        $frontStore = null;

        foreach ($this->storeManager->getStores() as $store) {
            if ($store->getIsActive()) {
                $frontStore = $store;
                break;
            }
        }

        return $frontStore->getBaseUrl() . 'report/view/';
    }
}
