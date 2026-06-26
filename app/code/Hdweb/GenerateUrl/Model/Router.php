<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Model;

use Hdweb\GenerateUrl\Helper\Data;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;

class Router implements RouterInterface
{
    /**
     * @param ActionFactory $actionFactory
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     * @param State $appState
     */
    public function __construct(
        private readonly ActionFactory $actionFactory,
        private readonly Data $helper,
        private readonly StoreManagerInterface $storeManager,
        private readonly State $appState,
    ) {
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        try {
            $areaCode = $this->appState->getAreaCode();
            if ($areaCode !== 'frontend') {
                return null;
            }
        } catch (\Exception $e) {
            // Area code not set yet, continue
        }

        $identifier = trim($request->getPathInfo(), '/');
        
        try {
            $storeId = (int) $this->storeManager->getStore()->getId();
        } catch (\Exception $e) {
            return null;
        }

        if (!$this->helper->isEnabled($storeId)) {
            return null;
        }

        $filePath = $this->helper->getFilePath($storeId);

        // Match exact path, path with .txt extension, or path/llms.txt
        $validPaths = [
            $filePath,
            $filePath . '.txt',
            $filePath . '/llms.txt',
        ];

        if (in_array($identifier, $validPaths, true)) {
            $request->setModuleName('generateurl')
                ->setControllerName('index')
                ->setActionName('index')
                ->setPathInfo('/generateurl/index/index');

            return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
        }

        return null;
    }
}

