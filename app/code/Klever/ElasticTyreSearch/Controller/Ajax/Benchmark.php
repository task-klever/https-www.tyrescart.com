<?php
namespace Klever\ElasticTyreSearch\Controller\Ajax;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Klever\ElasticTyreSearch\Model\BenchmarkService;

class Benchmark implements HttpGetActionInterface
{
    /** @var RequestInterface */
    private $request;

    /** @var JsonFactory */
    private $jsonFactory;

    /** @var BenchmarkService */
    private $benchmarkService;

    public function __construct(
        RequestInterface $request,
        JsonFactory      $jsonFactory,
        BenchmarkService $benchmarkService
    ) {
        $this->request          = $request;
        $this->jsonFactory      = $jsonFactory;
        $this->benchmarkService = $benchmarkService;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $query  = trim(strip_tags(substr((string) $this->request->getParam('q', 'tyre'), 0, 100)));
        $runs   = max(1, min(10, (int) $this->request->getParam('runs', 3)));

        if (strlen($query) < 2) {
            return $result->setData(['error' => 'Query too short. Use ?q=bridgestone&runs=3']);
        }

        try {
            $data = $this->benchmarkService->run($query, $runs);
        } catch (\Exception $e) {
            return $result->setData(['error' => $e->getMessage()]);
        }

        return $result->setData($data);
    }
}
