<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Controller;

use BitExpert\ForceCustomerLogin\Api\Controller\LoginCheckInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RouterInterface;

/**
 * Class LoginRouter
 *
 * @package BitExpert\ForceCustomerLogin\Controller
 */
class LoginRouter implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;
    /**
     * @var LoginCheckInterface
     */
    private $loginCheck;

    /**
     * LoginRouter constructor.
     *
     * @param ActionFactory $actionFactory
     * @param LoginCheckInterface $loginCheck
     * @throws \InvalidArgumentException
     */
    public function __construct(
        ActionFactory $actionFactory,
        LoginCheckInterface $loginCheck
    ) {
        $this->actionFactory = $actionFactory;
        $this->loginCheck = $loginCheck;
    }

    /**
     * @inheritDoc
     * @return ActionInterface|void
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if ($this->loginCheck->execute()) {
            /** @var \Magento\Framework\App\Request\Http $request */
            $request->setDispatched(true);
            return $this->actionFactory->create(\Magento\Framework\App\Action\Redirect::class);
        }
    }
}
