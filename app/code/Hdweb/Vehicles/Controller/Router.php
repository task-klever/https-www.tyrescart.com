<?php

namespace Hdweb\Vehicles\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url;

class Router implements RouterInterface
{
    protected $actionFactory;
    protected $eventManager;
    protected $response;
    protected $dispatched;
    protected $storeManager;
    protected $url;


    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Url $url
    ) {

        $this->actionFactory = $actionFactory;
        $this->response = $response;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->url = $url;
    }

    public function match(RequestInterface $request)
    {
        if (!$this->dispatched) {
            $urlKey = trim($request->getPathInfo(), '/');
            $route = 'tyres/cars';

            // 301 redirect old car-brands URLs to new tyres/cars URLs
            if (strpos($urlKey, 'car-brands') !== false) {
                $baseUrl = $this->url->getBaseUrl();
                $newUrl = $baseUrl . str_replace('car-brands', $route, rtrim($urlKey, '/'));
                $this->response->setRedirect($newUrl, 301);
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Redirect',
                    ['request' => $request]
                );
            }

            // Strip trailing slash redirect
            if (strpos($urlKey, $route) !== false && substr($request->getPathInfo(), -1) === '/') {
                $baseUrl = $this->url->getBaseUrl();
                $newUrl = $baseUrl . $urlKey;
                $this->response->setRedirect($newUrl, 301);
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Redirect',
                    ['request' => $request]
                );
            }

            // Match tyres/cars (index page)
            if ($urlKey == $route) {
                $request->setModuleName('vehicles')
                    ->setControllerName('vehicles')
                    ->setActionName('index')
                    ->setParam('route', $route);
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                $this->dispatched = true;
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                );
            }

            // Match tyres/cars/{make} and tyres/cars/{make}/{model}
            if (strpos($urlKey, $route . '/') === 0) {
                $remaining = substr($urlKey, strlen($route . '/'));
                $parts = explode('/', $remaining);

                if (count($parts) == 1 && $parts[0] !== 'ajax') {
                    // tyres/cars/{make}
                    $make = $parts[0];
                    $request->setModuleName('vehicles')
                        ->setControllerName('vehicles')
                        ->setActionName('make')
                        ->setParams(array(
                            'make' => $make,
                            'route' => $route,
                        ));
                    $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                    );
                } elseif (count($parts) == 2) {
                    // tyres/cars/{make}/{model}
                    $make = $parts[0];
                    $model = $parts[1];
                    $request->setModuleName('vehicles')
                        ->setControllerName('vehicles')
                        ->setActionName('model')
                        ->setParams(array(
                            'make' => $make,
                            'model' => $model,
                        ));
                    $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                    );
                } else {
                    return null;
                }
            }
        }
    }
}
