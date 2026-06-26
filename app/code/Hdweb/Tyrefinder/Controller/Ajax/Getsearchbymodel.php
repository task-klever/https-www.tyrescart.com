<?php
namespace Hdweb\Tyrefinder\Controller\Ajax;

use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class Getsearchbymodel extends \Magento\Framework\App\Action\Action
{
    protected $_resource;
    protected $resultJsonFactory;
    protected $finderhelper;
    protected $config;
    protected $storeManager;
    protected $registry;
    protected $productRepository;
    protected $categoryRepository;
    protected $productCollectionFactory;
    protected $flatWheelData;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Hdweb\Tyrefinder\Helper\Data $finderhelper,
        \Magento\Eav\Model\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        FlatWheelData $flatWheelData
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resource = $resource;
        $this->finderhelper = $finderhelper;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->flatWheelData = $flatWheelData;
        parent::__construct($context);
    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/GetSearchByModel_' . date('d-m-y') . '.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('GetSearchByModel');

        // Check if request is from car-wheels category
        $isCarWheelsPage = false;

        // Check referer URL - must contain /car-wheels as category path
        $referer = $this->getRequest()->getHeader('Referer');
        $refererPath = '';
        if ($referer) {
            // Parse URL to get path
            $refererPath = parse_url($referer, PHP_URL_PATH);
            if ($refererPath) {
                // Check if path contains /car-wheels (exact match or as category path)
                if (preg_match('#(/[a-z]{2}/)?car-wheels(/|$)#', $refererPath)) {
                    $isCarWheelsPage = true;
                    $logger->info('Detected car-wheels from referer path: ' . $refererPath);
                }
            }
        }

        // Check if current product belongs to car-wheels category (from registry)
        $currentProduct = $this->registry->registry('current_product');
        if ($currentProduct) {
            $productCategories = $currentProduct->getCategoryIds();
            if (!empty($productCategories)) {
                try {
                    foreach ($productCategories as $categoryId) {
                        $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
                        $categoryUrlKey = $category->getUrlKey();
                        if ($categoryUrlKey === 'car-wheels') {
                            $isCarWheelsPage = true;
                            $logger->info('Product belongs to car-wheels category: ' . $categoryId);
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    $logger->info('Error checking product category: ' . $e->getMessage());
                }
            }
        }

        // If product not in registry, try to load from referer URL
        if (!$isCarWheelsPage && $referer) {
            try {
                $refererPathParsed = parse_url($referer, PHP_URL_PATH);
                if ($refererPathParsed && !preg_match('#(/[a-z]{2}/)?car-wheels#', $refererPathParsed)) {
                    $pathParts = explode('/', trim($refererPathParsed, '/'));
                    $urlKey = end($pathParts);

                    if ($urlKey && $urlKey !== 'car-wheels' && strlen($urlKey) > 0) {
                        $storeId = $this->storeManager->getStore()->getId();
                        $productCollection = $this->productCollectionFactory->create()
                            ->addAttributeToFilter('url_key', $urlKey)
                            ->addAttributeToSelect('category_ids')
                            ->addStoreFilter($storeId)
                            ->setPageSize(1);

                        if ($productCollection->getSize() > 0) {
                            $product = $productCollection->getFirstItem();
                            $productCategories = $product->getCategoryIds();

                            $logger->info('Product found from referer URL key: ' . $urlKey . ', Categories: ' . implode(',', $productCategories));

                            if (!empty($productCategories)) {
                                $categoriesToCheck = $productCategories;
                                $checkedCategories = [];
                                $foundCarWheels = false;

                                while (!empty($categoriesToCheck) && !$foundCarWheels) {
                                    $categoryId = array_shift($categoriesToCheck);

                                    if (in_array($categoryId, $checkedCategories)) {
                                        continue;
                                    }

                                    $checkedCategories[] = $categoryId;

                                    try {
                                        $category = $this->categoryRepository->get($categoryId, $storeId);
                                        $categoryUrlKey = $category->getUrlKey();

                                        $logger->info('Checking category ID: ' . $categoryId . ', URL Key: ' . $categoryUrlKey);

                                        if ($categoryUrlKey === 'car-wheels') {
                                            $isCarWheelsPage = true;
                                            $foundCarWheels = true;
                                            $logger->info('Product from referer belongs to car-wheels category: ' . $categoryId . ' (URL Key: ' . $urlKey . ')');
                                            break;
                                        }

                                        $parentIds = $category->getParentIds();
                                        if (!empty($parentIds)) {
                                            foreach ($parentIds as $parentId) {
                                                if ($parentId > 1 && !in_array($parentId, $checkedCategories)) {
                                                    $categoriesToCheck[] = $parentId;
                                                }
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        $logger->info('Error loading category ' . $categoryId . ': ' . $e->getMessage());
                                    }
                                }
                            }
                        } else {
                            $logger->info('Product not found with URL key: ' . $urlKey);
                        }
                    }
                }
            } catch (\Exception $e) {
                $logger->info('Error loading product from referer: ' . $e->getMessage());
            }
        }

        // Check if current category is car-wheels
        $currentCategory = $this->registry->registry('current_category');
        if ($currentCategory) {
            $categoryUrlKey = $currentCategory->getUrlKey();
            if ($categoryUrlKey === 'car-wheels') {
                $isCarWheelsPage = true;
                $logger->info('Current category is car-wheels: ' . $currentCategory->getId());
            }
        }

        // Also check if a category parameter is explicitly passed
        $categoryParam = $this->getRequest()->getParam('category');
        if ($categoryParam && $categoryParam === 'car-wheels') {
            $isCarWheelsPage = true;
        }

        // Log for debugging
        $logger->info('Referer: ' . ($referer ? $referer : 'empty'));
        $logger->info('RefererPath: ' . ($refererPath ? $refererPath : 'empty'));
        $logger->info('CategoryParam: ' . ($categoryParam ? $categoryParam : 'empty'));
        $logger->info('CurrentProduct: ' . ($currentProduct ? $currentProduct->getId() : 'empty'));
        $logger->info('CurrentCategory: ' . ($currentCategory ? $currentCategory->getId() : 'empty'));
        $logger->info('isCarWheelsPage: ' . ($isCarWheelsPage ? 'true' : 'false'));

        $widthattributeCode = 'width';
        $widthattribute = $this->config->getAttribute('catalog_product', $widthattributeCode);
        $widhtOptions = $widthattribute->getSource()->getAllOptions();

        $heightattributeCode = 'height';
        $heightattribute = $this->config->getAttribute('catalog_product', $heightattributeCode);
        $heightOptions = $heightattribute->getSource()->getAllOptions();

        $rimtattributeCode = 'rim';
        $rimattribute = $this->config->getAttribute('catalog_product', $rimtattributeCode);
        $rimOptions = $rimattribute->getSource()->getAllOptions();

        $response = array();
        $make = $this->getRequest()->getParam('make');
        $model = $this->getRequest()->getParam('model');
        $year = $this->getRequest()->getParam('year');
        $modification = $this->getRequest()->getParam('modification');

        // Old: External API call to Klever/wheel-size
        // $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
        // $searchby_model_url = "https://wheel-api.klever.ae/v1/search/by_model/?user_key=".$wheelApiKey."&make=".$make."&model=".$model."&year=".$year."&modification=".$modification."";
        // $logger->info('execute : klever api url - ' . $searchby_model_url);
        // $modelengine = file_get_contents($searchby_model_url);
        // $logger->info('execute : klever api called, response length - ' . strlen($modelengine));
        // $modelengine = json_decode($modelengine);
        //
        // if(empty($modelengine) || empty($modelengine->data)) {
        //     $wheelApiKey = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('hdwebapi/general/wheelsize_api_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        //     $searchby_model_url = "https://api.wheel-size.com/v2/search/by_model/?user_key=".$wheelApiKey."&make=".$make."&model=".$model."&year=".$year."&modification=".$modification."";
        //     $modelengine = file_get_contents($searchby_model_url);
        //     $modelengine = json_decode($modelengine);
        // }

        // New: Read from Klever VehicleTyresGuide flat_wheel_data table
        $result = $this->flatWheelData->getModifications($make, $model, (int)$year);
        $modificationsData = $result['rows'];

        // Filter by modification slug if provided
        if (!empty($modification)) {
            $modificationsData = array_filter($modificationsData, function($row) use ($modification) {
                return ($row['modification_slug'] ?? '') === $modification;
            });
            $modificationsData = array_values($modificationsData);
        }

        $logger->info('execute : flat_wheel_data query, rows found - ' . count($modificationsData));

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        // Engine HTML generation
        $Engines = array();
        $engineHtml = "";

        foreach ($modificationsData as $key => $row) {
            $trim=array();
            $name = ($row['start_year'] ?? '') . '-' . ($row['end_year'] ?? '');
            $slug = $row['modification_slug'] ?? '';
            $trim['name'] = $row['trim'] ?? '';
            $trim['power'] = $row['power_hp'] ?? '';
            $Engines[$name][$slug] = $trim;
        }

        foreach ($Engines as $key => $country) {
            $engineHtml .= "<li>" . $key . "</li>";
            foreach ($country as $slugkey => $slugvalue) {
                $engineHtml .= '<li class="li-search"><a href="javascript:void(0)" class="button button-primary-grey custom-size button-block button-rounded opacity button-text-overflow" onclick="getTyreSizes(\'' . $slugkey . '\',\'' . $slugvalue['name'] . '\')" >' . $slugvalue['name'] . '<sup class="lightsup" title="248hp | 185kW | 252PS">'.$slugvalue['power'].'hp</sup></a><span id="autosearch-span" style="display:none">'.$slugvalue['name'].' '. $slugvalue['power'].'hp</span></li>';
            }
            $engineHtml .= "</ul></div></div>";
        }

        // Tire size HTML generation
        $enginesTyre = array();
        $enginesTyreArray = array();

        $wheelTyre = array();
        $wheelTyreRim = array();
        $enginesTyreRim = array();
        $wheelsTyreArray = array();

        foreach ($modificationsData as $key => $row) {
            $slug = $row['modification_slug'] ?? '';
            $alltyresize = array();
            $tireData = array();
            $wheelData = array();

            // Map flat_wheel_data columns to the old API structure
            $frontTireWidth = $row['front_tire_width'] ?? null;
            $frontTireAspectRatio = $row['front_tire_aspect_ratio'] ?? null;
            $frontRimDiameter = $row['front_rim_diameter'] ?? null;
            $frontLoadIndex = $row['front_load_index'] ?? null;
            $frontSpeedIndex = $row['front_speed_index'] ?? null;
            $frontRimWidth = $row['front_rim_width'] ?? null;
            $frontRimOffset = $row['front_rim_offset'] ?? null;
            $frontRim = $row['front_rim'] ?? null;
            $frontTire = $row['front_tire'] ?? null;

            $rearTireWidth = $row['rear_tire_width'] ?? null;
            $rearTireAspectRatio = $row['rear_tire_aspect_ratio'] ?? null;
            $rearRimDiameter = $row['rear_rim_diameter'] ?? null;
            $rearLoadIndex = $row['rear_load_index'] ?? null;
            $rearSpeedIndex = $row['rear_speed_index'] ?? null;
            $rearRimWidth = $row['rear_rim_width'] ?? null;
            $rearRimOffset = $row['rear_rim_offset'] ?? null;
            $rearRim = $row['rear_rim'] ?? null;

            if (empty($frontTireWidth)) {
                continue;
            }

            $frontTireStr = $frontTireWidth . '/' . $frontTireAspectRatio . 'R' . $frontRimDiameter;

            if ($frontLoadIndex) {
                $frontTireStr .= ' ' . '<span class="speed-index-label speed-index-label-1">'.$frontLoadIndex.''.$frontSpeedIndex.'</span>';
            }

            $rearTireStr = $rearTireWidth ?
                $rearTireWidth . '/' . $rearTireAspectRatio . 'R' . $rearRimDiameter :
                null;

            if ($rearLoadIndex) {
                $rearTireStr .= ' ' . '<span class="speed-index-label speed-index-label-2">'.$rearLoadIndex.''.$rearSpeedIndex.'</span>';
            }

            // Get attribute values
            $selectedWidthkey = array_search($frontTireWidth, array_column($widhtOptions, 'label'));
            $selectedHeightkey = array_search($frontTireAspectRatio, array_column($heightOptions, 'label'));
            $selectedRimkey = array_search($frontRimDiameter, array_column($rimOptions, 'label'));

            if ($rearTireWidth) {
                $rearselectedWidthkey = array_search($rearTireWidth, array_column($widhtOptions, 'label'));
                $rearselectedHeightkey = array_search($rearTireAspectRatio, array_column($heightOptions, 'label'));
                $rearselectedRimkey = array_search($rearRimDiameter, array_column($rimOptions, 'label'));
            }

            $fronttire = str_replace('Z', '', $frontTire ?? '');

            $dupeKey = $fronttire . '|' . ($row['rear_tire'] ?? '');
            if (in_array($dupeKey, $alltyresize)) {
                continue;
            }
            $alltyresize[] = $dupeKey;

            // Prepare display
            $isStock = isset($row['is_stock']) ? (bool)$row['is_stock'] : false;
            $sizeLabel = $isStock ? 'FACTORY SIZE' : 'OPTIONAL SIZE';
            $labelClass = $isStock ? 'factory-label' : 'optional-label';
            $rimSize = $rearRimDiameter ?? $frontRimDiameter;
            $oemClass = $isStock ? 'oem' : '';

            // Runflat - check if front_tire_full contains "RunFlat" or similar
            $runflat = false;
            $frontTireFull = $row['front_tire_full'] ?? '';
            if (stripos($frontTireFull, 'runflat') !== false || stripos($frontTireFull, 'run flat') !== false) {
                $runflat = true;
            }
            $runflatLabel = $runflat ? '<img class="runflat-logo" src="'.$mediaUrl.'images/icons/run-flat.svg" alt="Run Flat" />': '';

            if ($rearTireWidth) {
                $tireDisplay = $rimSize . '" | ' . $frontTireStr . ' ' . $rearTireStr;
                $titleDisplay = $frontTireStr . '    ' . $rearTireStr;
                $onclickParams = "'" . $frontTireWidth . "','" .
                                $frontTireAspectRatio . "','" .
                                $frontRimDiameter . "','" .
                                $rearTireWidth . "','" .
                                $rearTireAspectRatio . "','" .
                                $rearRimDiameter . "'";
                $sizeClass = 'front-rear-size';
            } else {
                $tireDisplay = $rimSize . '" | ' . $frontTireStr;
                $titleDisplay = $frontTireStr;
                $onclickParams = "'" . $frontTireWidth . "','" .
                                $frontTireAspectRatio . "','" .
                                $frontRimDiameter . "'";
                $sizeClass = '';
            }

            // Build wheel onclick parameters
            $frontWheel = $frontRim;

            // Build tyre size strings for wheel display
            $frontTireSize = $frontTireWidth . '/' . $frontTireAspectRatio . 'R' . $frontRimDiameter;
            $rearTireSize = $rearTireWidth
                ? $rearTireWidth . '/' . $rearTireAspectRatio . 'R' . $rearRimDiameter
                : '';

            if ($rearRimWidth) {
                $rearWheel = $rearRim;
                $wheelDisplay = $frontWheel . ' | ' . $frontTireSize . '<br>' . $rearWheel . ' | ' . $rearTireSize;
                $wheelTitleDisplay = $frontWheel . ' | ' . $frontTireSize . '    ' . $rearWheel . ' | ' . $rearTireSize;
                $onclickParams1 = "'" . $frontRimWidth . "','" .
                                $frontRimDiameter . "','" .
                                $frontRimOffset . "','" .
                                $frontTireWidth . "','" .
                                $frontTireAspectRatio . "','" .
                                $rearRimWidth . "','" .
                                $rearRimDiameter . "','" .
                                $rearRimOffset . "','" .
                                $rearTireWidth . "','" .
                                $rearTireAspectRatio . "'";
                $wheelSizeClass = 'front-rear-size';
            } else {
                $wheelDisplay = $frontWheel . ' | ' . $frontTireSize;
                $wheelTitleDisplay = $frontWheel . ' | ' . $frontTireSize;
                $onclickParams1 = "'" . $frontRimWidth . "','" .
                                $frontRimDiameter . "','" .
                                $frontRimOffset . "','" .
                                $frontTireWidth . "','" .
                                $frontTireAspectRatio . "'";
                $wheelSizeClass = '';
            }

            $logger->info('onclickParams1: ' . json_encode($onclickParams1));

            // Store tire data in array for sorting
            $tireData[] = [
                'rim_diameter' => $rimSize,
                'tireHtml' => '<li class="'.$oemClass.' '.$slug.' li-search" >
                                <a href="javascript:void(0)" class="button button-primary-grey custom-size button-block button-rounded opacity button-text-overflow '.$sizeClass.'"
                                    onclick="showproduct('.$onclickParams.')">
                                    <div class="tire-size-label '.$labelClass.'">'.$sizeLabel.'</div>
                                    <span class="runflat-label">'.$runflatLabel.'</span>
                                    '.$tireDisplay.'
                                </a>
                                <span id="autosearch-span" style="display:none">'.$titleDisplay.'</span>
                            </li>',
                'titleDisplay' => $titleDisplay
            ];

            $wheelData[] = [
                'rim_diameter' => $rimSize,
                'tireHtml' => '<li class="'.$oemClass.' '.$slug.' li-search" >
                                <a href="javascript:void(0)" class="button button-primary-grey custom-size button-block button-rounded opacity button-text-overflow '.$wheelSizeClass.'"
                                    onclick="showWheelProduct('.$onclickParams1.')">
                                    <div class="tire-size-label '.$labelClass.'">'.$sizeLabel.'</div>
                                    <span class="runflat-label">'.$runflatLabel.'</span>
                                    '.$wheelDisplay.'
                                </a>
                                <span id="autosearch-span" style="display:none">'.$wheelTitleDisplay.'</span>
                            </li>',
                'titleDisplay' => $titleDisplay
            ];

            // Sort by rim diameter
            usort($tireData, function($a, $b) {
                return $a['rim_diameter'] <=> $b['rim_diameter'];
            });

            usort($wheelData, function($a, $b) {
                return $a['rim_diameter'] <=> $b['rim_diameter'];
            });

            // Only populate tire data if NOT car-wheels page
            if (!$isCarWheelsPage) {
                foreach ($tireData as $tireItem) {
                    if (isset($enginesTyre[$tireItem['titleDisplay']])) {
                        $enginesTyre[$tireItem['titleDisplay']] .= $tireItem['tireHtml'];
                    } else {
                        $enginesTyre[$tireItem['titleDisplay']] = $tireItem['tireHtml'];
                    }
                    $enginesTyreRim[$tireItem['titleDisplay']] = (float)$tireItem['rim_diameter'];
                }
            }

            // Only populate wheel data if car-wheels page
            if ($isCarWheelsPage) {
                foreach ($wheelData as $wheelItem) {
                    if (isset($wheelTyre[$wheelItem['titleDisplay']])) {
                        $wheelTyre[$wheelItem['titleDisplay']] .= $wheelItem['tireHtml'];
                    } else {
                        $wheelTyre[$wheelItem['titleDisplay']] = $wheelItem['tireHtml'];
                    }
                    $wheelTyreRim[$wheelItem['titleDisplay']] = (float)$wheelItem['rim_diameter'];
                }
            }
        }

        // Sort by rim diameter before converting to indexed arrays
        if ($isCarWheelsPage) {
            uksort($wheelTyre, function($a, $b) use ($wheelTyreRim) {
                return ($wheelTyreRim[$a] ?? 0) <=> ($wheelTyreRim[$b] ?? 0);
            });
        } else {
            uksort($enginesTyre, function($a, $b) use ($enginesTyreRim) {
                return ($enginesTyreRim[$a] ?? 0) <=> ($enginesTyreRim[$b] ?? 0);
            });
        }

        // Convert to indexed arrays
        if ($isCarWheelsPage) {
            foreach($wheelTyre as $key => $wheelTyreValues) {
                $enginesTyreArray[] = $wheelTyreValues;
            }
            $wheelsTyreArray = array();
        } else {
            foreach($enginesTyre as $key => $enginesTyreValues) {
                $enginesTyreArray[] = $enginesTyreValues;
            }
            $wheelsTyreArray = array();
        }

        $logger->info('enginesTyreArray: ' . json_encode($enginesTyreArray));
        $logger->info('wheelsTyreArray: ' . json_encode($wheelsTyreArray));
        $logger->info('isCarWheelsPage: ' . ($isCarWheelsPage ? 'true' : 'false'));

        $response['engineHtml'] = $engineHtml;
        $response['enginesTyre'] = $enginesTyreArray;
        $response['wheelsTyre'] = $wheelsTyreArray;
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
