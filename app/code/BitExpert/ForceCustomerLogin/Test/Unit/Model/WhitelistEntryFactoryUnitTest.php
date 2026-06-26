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

use BitExpert\ForceCustomerLogin\Model\WhitelistEntry;
use BitExpert\ForceCustomerLogin\Model\WhitelistEntryFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class WhitelistEntryFactoryUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Model
 */
class WhitelistEntryFactoryUnitTest extends TestCase
{
    /**
     * @test
     */
    public function createEntitySuccessfully()
    {
        $expectedEntity = $this->createMock(WhitelistEntry::class);

        $om = $this->getObjectManager();
        $om->expects($this->once())
            ->method('create')
            ->with('\\BitExpert\\ForceCustomerLogin\\Model\\WhitelistEntry', ['foo' => 'bar'])
            ->willReturn($expectedEntity);

        $factory = new WhitelistEntryFactory($om);

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
