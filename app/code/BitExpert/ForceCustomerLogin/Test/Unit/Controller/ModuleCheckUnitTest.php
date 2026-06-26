<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Test\Unit\Controller;

use BitExpert\ForceCustomerLogin\Controller\ModuleCheck;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ModuleCheckUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Controller
 */
class ModuleCheckUnitTest extends TestCase
{
    /**
     * @test
     */
    public function moduleEnabledSuccessfully()
    {
        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                ModuleCheck::MODULE_CONFIG_ENABLED,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(true);

        $moduleCheck = new ModuleCheck($scopeConfig);

        $this->assertTrue($moduleCheck->isModuleEnabled());
    }

    /**
     * @test
     */
    public function moduleDisabledSuccessfully()
    {
        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                ModuleCheck::MODULE_CONFIG_ENABLED,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(false);

        $moduleCheck = new ModuleCheck($scopeConfig);

        $this->assertFalse($moduleCheck->isModuleEnabled());
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
}
