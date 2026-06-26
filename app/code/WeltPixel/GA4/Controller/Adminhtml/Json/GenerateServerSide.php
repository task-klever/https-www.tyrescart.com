<?php
namespace WeltPixel\GA4\Controller\Adminhtml\Json;

use Magento\Backend\App\Action;

/**
 * Class Generate
 * @package WeltPixel\GA4\Controller\Adminhtml\Json
 */
class GenerateServerSide extends Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \WeltPixel\GA4\Model\JsonGeneratorServerSide
     */
    protected $jsonGeneratorServerSide;

    /**
     * Version constructor.
     *
     * @param \WeltPixel\GA4\Model\JsonGeneratorServerSide $jsonGeneratorServerSide
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \WeltPixel\GA4\Model\JsonGeneratorServerSide $jsonGeneratorServerSide,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->jsonGeneratorServerSide = $jsonGeneratorServerSide;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $jsonUrl = null;
        $msg = $this->_validateParams($params);

        if (!count($msg)) {
            try {
                $jsonUrl = $this->jsonGeneratorServerSide->
                generateServerSideJson(
                    trim($params['account_id'] ?? ''),
                    trim($params['container_id'] ?? ''),
                    trim($params['public_id'] ?? ''),
                    trim($params['measurement_id'] ?? '')
                );
                $msg[] = __('Server Side Json was generated successfully. You can download the file by clicking on the Download Server Side Json button.');
            } catch (\Exception $ex) {
                $msg[] = $ex->getMessage();
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData([
            'msg' => $msg,
            'jsonUrl' => $jsonUrl
        ]);
        return $resultJson;
    }

    /**
     * @param $params
     * @return array
     */
    protected function _validateParams($params)
    {
        $accountId = $params['account_id'] ?? '';
        $containerId = $params['container_id'] ?? '';
        $publicId = $params['public_id'] ?? '';
        $measurementId = $params['measurement_id'] ?? '';

        $msg = [];

        if (!strlen(trim($accountId))) {
            $msg[] = __('Server Account ID must be specified');
        }

        if (!strlen(trim($containerId))) {
            $msg[] = __('Server Container ID must be specified');
        }

        if (!strlen(trim($publicId))) {
            $msg[] = __('Server Public ID must be specified');
        }

        if (!strlen(trim($measurementId))) {
            $msg[] = __('Measurement ID must be specified');
        }


        return $msg;
    }

}
