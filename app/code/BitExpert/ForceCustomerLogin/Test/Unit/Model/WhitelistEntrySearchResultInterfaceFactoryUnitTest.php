<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Test\Unit\Model;

use BitExpert\ForceCustomerLogin\Api\Data\Collection\WhitelistEntrySearchResultInterface;
use BitExpert\ForceCustomerLogin\Model\WhitelistEntrySearchResultInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class WhitelistEntrySearchResultInterfaceFactoryUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Model
 */
class WhitelistEntrySearchResultInterfaceFactoryUnitTest extends TestCase
{
    /**
     * @test
     */
    public function createEntitySuccessfully()
    {
        $expectedEntity = $this->createMock(WhitelistEntrySearchResultInterface::class);

        $om = $this->getObjectManager();
        $om->expects($this->once())
            ->method('create')
            ->with(
                '\\BitExpert\\ForceCustomerLogin\\Api\\Data\\Collection\\WhitelistEntrySearchResultInterface',
                ['foo' => 'bar']
            )
            ->willReturn($expectedEntity);

        $factory = new WhitelistEntrySearchResultInterfaceFactory($om);

        $this->assertEquals($expectedEntity, $factory->create(['foo' => 'bar']));
    }

    /**
     * @return MockObject|ObjectManagerInterface
     */
    private function getObjectManager()
    {
        return $this->createMock(ObjectManagerInterface::class);
    }
}
