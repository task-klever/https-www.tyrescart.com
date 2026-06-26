<?php
namespace WeltPixel\GA4\Controller\Adminhtml\Json;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use WeltPixel\GA4\Model\JsonGeneratorServerSide;

/**
 * Class Download
 * @package WeltPixel\GA4\Controller\Adminhtml\Json
 */
class DownloadServerSide extends Action
{

    /**
     * @var Http
     */
    protected $http;

    /**
     * @var JsonGeneratorServerSide
     */
    protected $jsonGeneratorServerSide;

    /**
     * Download constructor.
     * @param Context $context
     * @param Http $http
     * @param JsonGeneratorServerSide $jsonGeneratorServerSide
     */
    public function __construct(
        Context $context,
        Http $http,
        JsonGeneratorServerSide $jsonGeneratorServerSide
    ) {
        parent::__construct($context);
        $this->http = $http;
        $this->jsonGeneratorServerSide = $jsonGeneratorServerSide;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws FileSystemException
     */
    public function execute()
    {
        $response = $this->jsonGeneratorServerSide->getGeneratedJsonContent();
        $this->http->getHeaders()->clearHeaders();
        $this->http->setHeader('Content-Type', 'application/json')
            ->setHeader("Content-Disposition", "attachment; filename=ga4ServerSideExport.json")
            ->setBody($response);
    }
}
