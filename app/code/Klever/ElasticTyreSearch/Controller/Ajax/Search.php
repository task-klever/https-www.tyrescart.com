<?php
namespace Klever\ElasticTyreSearch\Controller\Ajax;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Klever\ElasticTyreSearch\Model\GlobalSearchService;
use Klever\ElasticTyreSearch\Plugin\NormalizeTyreSizeQuery;

class Search implements HttpGetActionInterface
{
    /** @var RequestInterface */
    private $request;

    /** @var JsonFactory */
    private $jsonFactory;

    /** @var GlobalSearchService */
    private $searchService;

    public function __construct(
        RequestInterface    $request,
        JsonFactory         $jsonFactory,
        GlobalSearchService $searchService
    ) {
        $this->request       = $request;
        $this->jsonFactory   = $jsonFactory;
        $this->searchService = $searchService;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $query  = NormalizeTyreSizeQuery::normalize(
            trim(strip_tags(substr((string) $this->request->getParam('q', ''), 0, 100)))
        );

        if (strlen($query) < 2) {
            return $result->setData([
                'products' => [], 'brands' => [], 'blogs' => [], 'vehicles' => [], 'cms' => [],
            ]);
        }

        try {
            $data = $this->searchService->search($query);
        } catch (\Exception $e) {
            return $result->setData(['error' => $e->getMessage()]);
        }

        return $result->setData($data);
    }
}
