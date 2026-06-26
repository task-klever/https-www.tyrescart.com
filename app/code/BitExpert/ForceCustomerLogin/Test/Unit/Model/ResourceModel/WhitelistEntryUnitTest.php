<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Test\Unit\Model\ResourceModel;

use BitExpert\ForceCustomerLogin\Model\ResourceModel\WhitelistEntry;
use Magento\Framework\Model\ResourceModel\Db\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class WhitelistEntryUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Model\Resource
 */
class WhitelistEntryUnitTest extends TestCase
{
    /**
     * @test
     */
    public function createEntitySuccessfully()
    {
        $resource = new WhitelistEntry($this->getDatabaseContext());
        $this->assertEquals('whitelist_entry_id', $resource->getIdFieldName());
    }

    /**
     * @return MockObject|\Magento\Framework\Model\ResourceModel\Db\Context
     */
    private function getDatabaseContext()
    {
        return $this->createMock(Context::class);
    }
}
