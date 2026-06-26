<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Test\Unit\Helper\Strategy;

use BitExpert\ForceCustomerLogin\Helper\Strategy\StrategyInterface;
use BitExpert\ForceCustomerLogin\Helper\Strategy\StrategyManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class StrategyManagerUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Helper\Strategy
 */
class StrategyManagerUnitTest extends TestCase
{
    /**
     * @test
     */
    public function getStrategyNamesSuccessfully()
    {
        /** @var $strategy1 MockObject|StrategyInterface */
        $strategy1 = $this->createMock(StrategyInterface::class);
        $strategy1->expects($this->once())
            ->method('getName')
            ->willReturn('Static');
        /** @var $strategy2 MockObject|StrategyInterface */
        $strategy2 = $this->createMock(StrategyInterface::class);
        $strategy2->expects($this->once())
            ->method('getName')
            ->willReturn('RegEx (All)');

        $manager = new StrategyManager([
            'default' => $strategy1,
            'regex-all' => $strategy2
        ]);

        $this->assertEquals(
            [
                'default' => 'Static',
                'regex-all' => 'RegEx (All)'
            ],
            $manager->getStrategyNames()
        );
    }

    /**
     * @test
     */
    public function getStrategyInstancesSuccessfully()
    {
        /** @var $strategy1 MockObject|StrategyInterface */
        $strategy1 = $this->createMock(StrategyInterface::class);
        $strategy1->expects($this->once())
            ->method('getName')
            ->willReturn('Static');
        /** @var $strategy2 MockObject|StrategyInterface */
        $strategy2 = $this->createMock(StrategyInterface::class);
        $strategy2->expects($this->once())
            ->method('getName')
            ->willReturn('RegEx (All)');

        $manager = new StrategyManager([
            'default' => $strategy1,
            'regex-all' => $strategy2
        ]);

        $this->assertEquals(
            [
                'default' => $strategy1,
                'regex-all' => $strategy2
            ],
            $manager->getStrategies()
        );
    }

    /**
     * @test
     */
    public function getStrategyInstanceSuccessfully()
    {
        /** @var $strategy1 MockObject|StrategyInterface */
        $strategy1 = $this->createMock(StrategyInterface::class);
        $strategy1->expects($this->once())
            ->method('getName')
            ->willReturn('Static');
        /** @var $strategy2 StrategyInterface|MockObject */
        $strategy2 = $this->createMock('\BitExpert\ForceCustomerLogin\Helper\Strategy\StrategyInterface');
        $strategy2->expects($this->once())
            ->method('getName')
            ->willReturn('RegEx (All)');

        $manager = new StrategyManager([
            'default' => $strategy1,
            'regex-all' => $strategy2
        ]);

        $this->assertFalse($manager->has('foo'));
        $this->assertTrue($manager->has('regex-all'));
        $this->assertEquals($strategy2, $manager->get('regex-all'));
    }

    /**
     * @test
     */
    public function getDefaultStrategyInstanceSuccessfully()
    {
        /** @var $strategy1 MockObject|StrategyInterface */
        $strategy1 = $this->createMock(StrategyInterface::class);
        $strategy1->expects($this->once())
            ->method('getName')
            ->willReturn('Static');
        /** @var $strategy2 MockObject|StrategyInterface */
        $strategy2 = $this->createMock(StrategyInterface::class);
        $strategy2->expects($this->once())
            ->method('getName')
            ->willReturn('RegEx (All)');

        $manager = new StrategyManager([
            'default' => $strategy1,
            'regex-all' => $strategy2
        ]);

        $this->assertFalse($manager->has('foo'));
        $this->assertTrue($manager->has('default'));
        $this->assertEquals($strategy1, $manager->get('foo'));
    }

    /**
     * @test
     */
    public function throwErrorOnMissingMatchinStrategyAndNoDefaultStrategy()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not load rule strategy with identifier "foo"');

        $manager = new StrategyManager([]);
        $manager->get('foo');
    }
}
