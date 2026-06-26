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

use BitExpert\ForceCustomerLogin\Api\Data\WhitelistEntryInterface;
use BitExpert\ForceCustomerLogin\Api\Repository\WhitelistRepositoryInterface;
use BitExpert\ForceCustomerLogin\Helper\Strategy\StrategyInterface;
use BitExpert\ForceCustomerLogin\Model\WhitelistDefaultInstaller;
use BitExpert\ForceCustomerLogin\Model\WhitelistDefaultPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class WhitelistDefaultInstallerUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Model
 */
class WhitelistDefaultInstallerUnitTest extends TestCase
{
    /**
     * @test
     * @dataProvider \BitExpert\ForceCustomerLogin\Test\DataProviders\WhitelistDataProvider::get()
     */
    public function installsSuccessfully($entries)
    {
        $poolMock = $this->createMock(WhitelistDefaultPool::class);
        $poolMock->expects($this->once())
            ->method('getEntries')
            ->willReturn($entries);

        $repositoryMock = $this->createMock(WhitelistRepositoryInterface::class);
        $repositoryMock->expects($this->exactly(count($entries)))
            ->method('createEntry');

        $installer = new WhitelistDefaultInstaller($poolMock, $repositoryMock);
        $installer->install();
    }
}
