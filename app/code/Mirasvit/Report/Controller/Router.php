<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report
 * @version   1.4.38
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\Report\Controller;


use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;


class Router implements RouterInterface
{
    private $actionFactory;

    public function __construct(ActionFactory $actionFactory)
    {
        $this->actionFactory = $actionFactory;
    }

    public function match(RequestInterface $request)
    {
        $pathInfo = $request->getPathInfo();

        if (strpos($pathInfo, 'report/view') !== false) {
            $parts = array_values(array_filter(explode('/', $pathInfo), 'trim'));

            if (count($parts) == 3) {
                $identifier = explode('.', $parts[2]);

                $params = [
                    'identifier' => $identifier[0] ?? null,
                    'format'     => $identifier[1] ?? null
                ];
            } else {
                $params = [
                    'identifier' => null,
                    'format'     => null
                ];
            }

            $request->setAlias(
                \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
                ltrim($request->getOriginalPathInfo(), '/')
            )
                ->setModuleName('report')
                ->setControllerName('report')
                ->setActionName('view')
                ->setParams($params);

            return $this->actionFactory->create(
                'Magento\Framework\App\Action\Forward'
            );
        }

        return false;
    }
}
