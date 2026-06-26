<?php


namespace TotalPay\Gateway\Controller\Url;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 *
 * Class Index
 * @package TotalPay\Gateway\Controller\Ipn
 */
class Response extends \TotalPay\Gateway\Controller\AbstractAction implements CsrfAwareActionInterface
{

    /**
     * Instantiate IPN model and pass IPN request to it
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }

        try {
            $ipn = $this->getObjectManager()->create(
                "TotalPay\\Gateway\\Model\\Ipn\\TotalPayGatewayIpn"
            );
            $data = $this->getRequest()->getPostValue();
            $this->_logger->debug('responseData mid value: ' . $data['||mid']);
            $params = explode('||', $data['||mid']);
            $dataArr = [];
            if (is_array($params)) {
                array_walk($params, function ($item, $key) use (&$dataArr) {
                    $param = explode('=', $item);
                    if (count($param) === 2) {
                        $dataArr[trim($param[0])] = trim($param[1]);
                    }
                });
            }
            $responseBody = $ipn->handleTotalPayGatewayNotification($dataArr);
            $this->getResponse()
                ->setHeader('Content-type', 'text/html')
                ->setBody($responseBody['body'])
                ->setHttpResponseCode($responseBody['code'])
                ->sendResponse();
        } catch (\Exception $e) {
            $this->getLogger()->debug($e->getMessage());
            $this->getResponse()->setHttpResponseCode(500);
        }
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
