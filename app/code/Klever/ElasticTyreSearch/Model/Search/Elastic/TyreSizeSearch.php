<?php
namespace Klever\ElasticTyreSearch\Model\Search\Elastic;

use Smile\ElasticsuiteCore\Api\Client\ClientInterface;
use Smile\ElasticsuiteCore\Search\Request\ContainerConfigurationFactory;
use Magento\Store\Model\StoreManagerInterface;

class TyreSizeSearch
{
    /** @var ClientInterface */
    private $client;

    /** @var ContainerConfigurationFactory */
    private $containerConfigFactory;

    /** @var StoreManagerInterface */
    private $storeManager;

    public function __construct(
        ClientInterface $client,
        ContainerConfigurationFactory $containerConfigFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->client                 = $client;
        $this->containerConfigFactory = $containerConfigFactory;
        $this->storeManager           = $storeManager;
    }

    /**
     * Return distinct tyre sizes matching the query prefix.
     * Uses wildcard on option_text_tyre_size (keyword analyzer field).
     *
     * @param string $query  Normalised tyre size prefix, e.g. "195/65"
     * @param int    $limit  Max number of suggestions
     * @return string[]
     */
    public function search(string $query, int $limit = 10): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();

        $containerConfig = $this->containerConfigFactory->create([
            'containerName' => 'quick_search_container',
            'storeId'       => $storeId,
        ]);

        $pattern = strtolower(str_replace(' ', '*', trim($query))) . '*';

        $esQuery = [
            'index' => $containerConfig->getIndexName(),
            'body'  => [
                'size'    => $limit * 3,
                '_source' => ['option_text_tyre_size'],
                'query'   => [
                    'wildcard' => [
                        'option_text_tyre_size' => ['value' => $pattern, 'case_insensitive' => true],
                    ],
                ],
            ],
        ];

        try {
            $result = $this->client->search($esQuery);
        } catch (\Exception $e) {
            return [];
        }

        $seen  = [];
        $sizes = [];
        foreach ($result['hits']['hits'] ?? [] as $hit) {
            $values = (array) ($hit['_source']['option_text_tyre_size'] ?? []);
            foreach ($values as $size) {
                $size = trim($size);
                if ($size === '' || isset($seen[$size])) {
                    continue;
                }
                $seen[$size] = true;
                $sizes[]     = $size;
                if (count($sizes) >= $limit) {
                    break 2;
                }
            }
        }

        sort($sizes);
        return $sizes;
    }
}
