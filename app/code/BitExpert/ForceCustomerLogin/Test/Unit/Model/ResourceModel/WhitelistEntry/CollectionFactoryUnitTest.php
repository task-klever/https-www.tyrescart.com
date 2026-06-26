<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Test\Unit\Model\ResourceModel\WhitelistEntry;

use BitExpert\ForceCustomerLogin\Model\ResourceModel\WhitelistEntry\Collection;
use BitExpert\ForceCustomerLogin\Model\ResourceModel\WhitelistEntry\CollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CollectionFactoryUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Model\Resource\WhitelistEntry
 */
class CollectionFactoryUnitTest extends TestCase
{
    /**
     * @test
     */
    public function createEntitySuccessfully()
    {
        $expectedEntity = $this->createMock(Collection::class);

        $om = $this->getObjectManager();
        $om->expects($this->once())
            ->method('create')
            ->with(
                '\\BitExpert\\ForceCustomerLogin\\Model\\ResourceModel\\WhitelistEntry\\Collection',
                ['foo' => 'bar']
            )
            ->willReturn($expectedEntity);

        $factory = new CollectionFactory($om);

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
