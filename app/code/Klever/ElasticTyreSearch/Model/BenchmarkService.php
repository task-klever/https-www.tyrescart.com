<?php
namespace Klever\ElasticTyreSearch\Model;

use Klever\ElasticTyreSearch\Model\Search\Elastic;
use Klever\ElasticTyreSearch\Model\Search\Mysql;

class BenchmarkService
{
    /** @var Elastic\ProductSearch */
    private $esProduct;

    /** @var Elastic\BrandSearch */
    private $esBrand;

    /** @var Elastic\BlogSearch */
    private $esBlog;

    /** @var Elastic\VehicleSearch */
    private $esVehicle;

    /** @var Elastic\CmsSearch */
    private $esCms;

    /** @var Mysql\ProductSearch */
    private $mysqlProduct;

    /** @var Mysql\BrandSearch */
    private $mysqlBrand;

    /** @var Mysql\BlogSearch */
    private $mysqlBlog;

    /** @var Mysql\VehicleSearch */
    private $mysqlVehicle;

    /** @var Mysql\CmsSearch */
    private $mysqlCms;

    public function __construct(
        Elastic\ProductSearch $esProduct,
        Elastic\BrandSearch   $esBrand,
        Elastic\BlogSearch    $esBlog,
        Elastic\VehicleSearch $esVehicle,
        Elastic\CmsSearch     $esCms,
        Mysql\ProductSearch   $mysqlProduct,
        Mysql\BrandSearch     $mysqlBrand,
        Mysql\BlogSearch      $mysqlBlog,
        Mysql\VehicleSearch   $mysqlVehicle,
        Mysql\CmsSearch       $mysqlCms
    ) {
        $this->esProduct    = $esProduct;
        $this->esBrand      = $esBrand;
        $this->esBlog       = $esBlog;
        $this->esVehicle    = $esVehicle;
        $this->esCms        = $esCms;
        $this->mysqlProduct = $mysqlProduct;
        $this->mysqlBrand   = $mysqlBrand;
        $this->mysqlBlog    = $mysqlBlog;
        $this->mysqlVehicle = $mysqlVehicle;
        $this->mysqlCms     = $mysqlCms;
    }

    /**
     * Run both ES and MySQL for all entity types and return timing comparison.
     */
    public function run(string $query, int $runs = 3): array
    {
        $pairs = [
            'products' => [$this->esProduct, $this->mysqlProduct],
            'brands'   => [$this->esBrand,   $this->mysqlBrand],
            'blogs'    => [$this->esBlog,     $this->mysqlBlog],
            'vehicles' => [$this->esVehicle,  $this->mysqlVehicle],
            'cms'      => [$this->esCms,      $this->mysqlCms],
        ];

        $results = [];

        foreach ($pairs as $type => [$es, $mysql]) {
            // Run multiple times and take the average to reduce noise
            $esTimes    = [];
            $mysqlTimes = [];
            $esData     = [];
            $mysqlData  = [];

            for ($i = 0; $i < $runs; $i++) {
                $t = microtime(true);
                $esData = $es->search($query);
                $esTimes[] = (microtime(true) - $t) * 1000;

                $t = microtime(true);
                $mysqlData = $mysql->search($query);
                $mysqlTimes[] = (microtime(true) - $t) * 1000;
            }

            $esAvg    = round(array_sum($esTimes) / $runs, 2);
            $mysqlAvg = round(array_sum($mysqlTimes) / $runs, 2);

            $results[$type] = [
                'elastic' => [
                    'avg_ms'   => $esAvg,
                    'all_ms'   => array_map(fn($t) => round($t, 2), $esTimes),
                    'count'    => count($esData),
                ],
                'mysql' => [
                    'avg_ms'   => $mysqlAvg,
                    'all_ms'   => array_map(fn($t) => round($t, 2), $mysqlTimes),
                    'count'    => count($mysqlData),
                ],
                'winner'  => $esAvg <= $mysqlAvg ? 'elastic' : 'mysql',
                'diff_ms' => round(abs($esAvg - $mysqlAvg), 2),
            ];
        }

        return [
            'query'   => $query,
            'runs'    => $runs,
            'results' => $results,
            'summary' => $this->buildSummary($results),
        ];
    }

    private function buildSummary(array $results): array
    {
        $winners = array_column($results, 'winner');
        $esWins    = count(array_filter($winners, fn($w) => $w === 'elastic'));
        $mysqlWins = count(array_filter($winners, fn($w) => $w === 'mysql'));

        return [
            'elastic_wins' => $esWins,
            'mysql_wins'   => $mysqlWins,
            'overall'      => $esWins >= $mysqlWins ? 'elastic' : 'mysql',
        ];
    }
}
