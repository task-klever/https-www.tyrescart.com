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
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class WhitelistEntryUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Model
 */
class WhitelistEntryUnitTest extends TestCase
{
    /**
     * @test
     */
    public function testGettersAndSetters()
    {
        $resource = $this->getResourceModel();
        $resource->expects($this->atLeastOnce())
            ->method('getIdFieldName')
            ->willReturn('whitelist_entry_id');

        $entity = new WhitelistEntry(
            $this->getContext(),
            $this->getRegistry(),
            $resource,
            $this->getDatabase(),
            []
        );

        $entity->setStoreId(42);
        $this->assertEquals(42, $entity->getStoreId());
        $entity->setLabel('label');
        $this->assertEquals('label', $entity->getLabel());
        $entity->setUrlRule('url-rule');
        $this->assertEquals('url-rule', $entity->getUrlRule());
        $entity->setStrategy('strategy');
        $this->assertEquals('strategy', $entity->getStrategy());
        $entity->setEditable(false);
        $this->assertFalse($entity->getEditable());
    }

    /**
     * @return MockObject|AbstractResource
     */
    private function getResourceModel()
    {
        return $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->setMethods([
                '_construct',
                'getConnection',
                'getIdFieldName'
            ])
            ->getMock();
    }

    /**
     * @return MockObject|Context
     */
    private function getContext()
    {
        return $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|Registry
     */
    private function getRegistry()
    {
        return $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|AbstractDb
     */
    private function getDatabase()
    {
        return $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
