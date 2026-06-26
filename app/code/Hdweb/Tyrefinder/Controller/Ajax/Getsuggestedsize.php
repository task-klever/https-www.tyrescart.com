<?php
namespace Hdweb\Tyrefinder\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
// use Magento\Framework\HTTP\Client\Curl;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class Getsuggestedsize extends Action
{
    protected $resultJsonFactory;
    // protected $curl;
    protected $flatWheelData;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        // Curl $curl
        FlatWheelData $flatWheelData
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        // $this->curl = $curl;
        $this->flatWheelData = $flatWheelData;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $width = $this->getRequest()->getParam('width');
        $height = $this->getRequest()->getParam('height');
        $rim = $this->getRequest()->getParam('rim');

        if (!$width || !$height || !$rim) {
            return $result->setData(['error' => true, 'message' => 'Missing parameters']);
        }

        try {
            // Old: External API call to Klever wheel-api
            // $apiKey = "49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9";
            // $url = "https://wheel-api.klever.ae/search.php?api_key={$apiKey}&width={$width}&height={$height}&rim={$rim}";
            // $this->curl->get($url);
            // $response = $this->curl->getBody();
            // return $result->setData([
            //     'error' => false,
            //     'data' => json_decode($response, true)
            // ]);

            // New: Read from Klever VehicleTyresGuide flat_wheel_data table
            $searchResult = $this->flatWheelData->searchByTyreSizeStaggered((int)$width, (int)$height, (int)$rim);
            $rows = $searchResult['rows'] ?? [];

            return $result->setData([
                'error' => false,
                'data' => [
                    'status' => !empty($rows) ? 'success' : 'error',
                    'message' => !empty($rows) ? '' : 'No results found',
                    'data' => $rows
                ]
            ]);
        } catch (\Exception $e) {
            return $result->setData(['error' => true, 'message' => $e->getMessage()]);
        }
    }
}
