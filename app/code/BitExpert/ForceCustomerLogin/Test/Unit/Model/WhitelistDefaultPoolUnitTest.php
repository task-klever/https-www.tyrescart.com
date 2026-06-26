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

use BitExpert\ForceCustomerLogin\Model\WhitelistDefaultPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class WhitelistDefaultPoolUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Model
 */
class WhitelistDefaultPoolUnitTest extends TestCase
{
    /**
     * @test
     * @dataProvider \BitExpert\ForceCustomerLogin\Test\DataProviders\WhitelistDataProvider::get()
     */
    public function getEntriesSuccessfully($entries)
    {
        $pool = new WhitelistDefaultPool($entries);
        $this->assertEquals($entries, $pool->getEntries());
    }
}
