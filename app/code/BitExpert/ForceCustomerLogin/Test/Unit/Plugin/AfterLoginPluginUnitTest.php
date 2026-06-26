<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Test\Unit\Plugin;

use BitExpert\ForceCustomerLogin\Controller\ModuleCheck;
use BitExpert\ForceCustomerLogin\Model\Session;
use BitExpert\ForceCustomerLogin\Plugin\AfterLoginPlugin;
use Magento\Customer\Controller\Account\LoginPost;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AfterLoginPluginUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Plugin
 */
class AfterLoginPluginUnitTest extends TestCase
{
    /**
     * @test
     */
    public function runAfterExecuteWithRedirectDashboardOptionEnabled()
    {
        $session = $this->getSession();
        $session->expects($this->never())
            ->method('getAfterLoginReferer');

        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(AfterLoginPlugin::REDIRECT_DASHBOARD_CONFIG)
            ->willReturn(AfterLoginPlugin::REDIRECT_DASHBOARD_ENABLED);

        $moduleCheck = $this->getModuleCheck();
        $moduleCheck->expects($this->once())
            ->method('isModuleEnabled')
            ->willReturn(true);

        $plugin = new AfterLoginPlugin(
            $session,
            $scopeConfig,
            $moduleCheck,
            'default-target-url'
        );

        $loginPost = $this->getLoginPost();
        $redirect = $this->getRedirect();
        $redirect->expects($this->never())
            ->method('setUrl');

        $this->assertEquals($redirect, $plugin->afterExecute($loginPost, $redirect));
    }
    /**
     * @test
     */
    public function runAfterExecuteWithSpecificTargetUrl()
    {
        $session = $this->getSession();
        $session->expects($this->once())
            ->method('getAfterLoginReferer')
            ->willReturn('specific-target-url');

        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(AfterLoginPlugin::REDIRECT_DASHBOARD_CONFIG)
            ->willReturn(AfterLoginPlugin::REDIRECT_DASHBOARD_DISABLED);

        $moduleCheck = $this->getModuleCheck();
        $moduleCheck->expects($this->once())
            ->method('isModuleEnabled')
            ->willReturn(true);

        $plugin = new AfterLoginPlugin(
            $session,
            $scopeConfig,
            $moduleCheck,
            'default-target-url'
        );

        $loginPost = $this->getLoginPost();
        $redirect = $this->getRedirect();
        $redirect->expects($this->once())
            ->method('setUrl')
            ->with('specific-target-url');

        $this->assertEquals($redirect, $plugin->afterExecute($loginPost, $redirect));
    }

    /**
     * @test
     */
    public function runAfterExecuteWithDefaultTargetUrl()
    {
        $session = $this->getSession();
        $session->expects($this->once())
            ->method('getAfterLoginReferer')
            ->willReturn(null);

        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(AfterLoginPlugin::REDIRECT_DASHBOARD_CONFIG)
            ->willReturn(AfterLoginPlugin::REDIRECT_DASHBOARD_DISABLED);

        $moduleCheck = $this->getModuleCheck();
        $moduleCheck->expects($this->once())
            ->method('isModuleEnabled')
            ->willReturn(true);

        $plugin = new AfterLoginPlugin(
            $session,
            $scopeConfig,
            $moduleCheck,
            'default-target-url'
        );

        $loginPost = $this->getLoginPost();
        $redirect = $this->getRedirect();
        $redirect->expects($this->once())
            ->method('setUrl')
            ->with('default-target-url');

        $this->assertEquals($redirect, $plugin->afterExecute($loginPost, $redirect));
    }

    /**
     * @test
     */
    public function runAfterExecuteWithModuleDisabled()
    {
        $session = $this->getSession();
        $session->expects($this->never())
            ->method('getAfterLoginReferer');

        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->never())
            ->method('getValue');

        $moduleCheck = $this->getModuleCheck();
        $moduleCheck->expects($this->once())
            ->method('isModuleEnabled')
            ->willReturn(false);

        $plugin = new AfterLoginPlugin(
            $session,
            $scopeConfig,
            $moduleCheck,
            'default-target-url'
        );

        $loginPost = $this->getLoginPost();
        $redirect = $this->getRedirect();
        $redirect->expects($this->never())
            ->method('setUrl');

        $this->assertEquals($redirect, $plugin->afterExecute($loginPost, $redirect));
    }

    /**
     * @return MockObject|Session
     */
    private function getSession()
    {
        return $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getAfterLoginReferer'
            ])
            ->getMock();
    }

    /**
     * @return MockObject|ScopeConfigInterface
     */
    private function getScopeConfig()
    {
        return $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|ModuleCheck
     */
    private function getModuleCheck()
    {
        return $this->getMockBuilder(ModuleCheck::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|LoginPost
     */
    private function getLoginPost()
    {
        return $this->getMockBuilder(LoginPost::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|Redirect
     */
    private function getRedirect()
    {
        return $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
