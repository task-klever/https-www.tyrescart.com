<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @author    Ecomteck <ecomteck@gmail.com>
 * @copyright 2016 Ecomteck
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Ecomteck\StoreLocator\Controller;

/**
 * Store locator routing (handling rewritten URL).
 *
 * @category Ecomteck
 * @package  Ecomteck_StoreLocator
 * @author   Ecomteck <ecomteck@gmail.com>
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    private $actionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Ecomteck\StoreLocator\Model\Url
     */
    private $urlModel;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\ActionFactory      $actionFactory Action factory.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager  Event manager.
     * @param \Ecomteck\StoreLocator\Model\Url             $urlModel      Store URL model.
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Ecomteck\StoreLocator\Model\Url $urlModel
    ) {
        $this->actionFactory = $actionFactory;
        $this->eventManager  = $eventManager;
        $this->urlModel      = $urlModel;
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request Request.
     *
     * @return NULL|\Magento\Framework\App\ActionInterface
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $action = null;

        $requestPath = trim($request->getPathInfo(), '/');
        $condition  = new \Magento\Framework\DataObject(['identifier' => $requestPath]);
        if ($this->matchStoreLocatorHome($requestPath)) {
            $this->eventManager->dispatch(
                'store_locator_search_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
            );

            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $requestPath)
                ->setModuleName('storelocator')
                ->setControllerName('index')
                ->setActionName('index');

            $action = $this->actionFactory->create('Magento\Framework\App\Action\Forward', ['request' => $request]);
        } elseif ($storeId = $this->matchStore($requestPath)) {
            $this->eventManager->dispatch(
                'store_locator_view_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
            );

            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $requestPath)
                ->setModuleName('storelocator')
                ->setControllerName('view')
                ->setActionName('index')
                ->setParam('id', $storeId);

            $action = $this->actionFactory->create('Magento\Framework\App\Action\Forward', ['request' => $request]);
        }

        return $action;
    }

    /**
     * Check if the current request path match the configured store locator home.
     *
     * @param string $requestPath Request path.
     *
     * @return boolean
     */
    private function matchStoreLocatorHome($requestPath)
    {
        return $this->urlModel->getRequestPathPrefix() == $requestPath;
    }

    /**
     * Check if the current request path match a store URL and returns its id.
     *
     * @param unknown $requestPath Request path.
     *
     * @return int|false
     */
    private function matchStore($requestPath)
    {
        $storeId       = false;
        $requestPathArray = explode('/', $requestPath);

        if (count($requestPathArray) && $this->matchStoreLocatorHome(current($requestPathArray))) {
            $storeId = $this->urlModel->checkIdentifier(end($requestPathArray));
        }

        return $storeId;
    }
}
