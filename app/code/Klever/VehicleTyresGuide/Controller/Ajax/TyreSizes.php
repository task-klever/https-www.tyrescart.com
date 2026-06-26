<?php

declare(strict_types=1);

namespace Klever\VehicleTyresGuide\Controller\Ajax;

use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\ScopeInterface;

class TyreSizes implements HttpPostActionInterface
{
    public function __construct(
        private readonly JsonFactory         $jsonFactory,
        private readonly FlatWheelData       $flatWheelData,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly RequestInterface    $request
    ) {
    }

    public function execute()
    {
        $enabled = $this->scopeConfig->isSetFlag(
            'klever_vehicle/general/enabled',
            ScopeInterface::SCOPE_STORE
        );

        if (!$enabled) {
            return $this->jsonFactory->create()->setData(['response' => '']);
        }

        $data    = $this->flatWheelData->getTyreSizes();
        $options = '';

        foreach ($data['rows'] as $row) {
            $width  = (int) $row['width'];
            $height = (int) $row['height'];
            $rim    = (int) $row['rim'];

            $label       = $width . '/' . $height . ' R' . $rim;
            $noSpace     = $width . '/' . $height . 'R' . $rim;
            $noSlashNoR  = $width . $height . $rim;
            $noSlash     = $width . $height . 'R' . $rim;
            $slashNoR    = $width . '/' . $height . '/' . $rim;
            $spacesNoR   = $width . ' ' . $height . ' ' . $rim;

            $lookup = implode(' ', [$noSpace, $noSlashNoR, $noSlash, $slashNoR, $spacesNoR, $label]);

            $options .= '<li value="' . $label . '" label="' . $label . '"'
                . ' data-lookup="' . $lookup . '"'
                . ' data-width="' . $width . '"'
                . ' data-height="' . $height . '"'
                . ' data-rim="' . $rim . '">'
                . $label . '</li>';
        }

        return $this->jsonFactory->create()->setData(['response' => $options]);
    }
}
