<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Test\Unit\Ui\Component\Listing\Column;

use BitExpert\ForceCustomerLogin\Helper\Strategy\StrategyInterface;
use BitExpert\ForceCustomerLogin\Helper\Strategy\StrategyManager;
use BitExpert\ForceCustomerLogin\Ui\Component\Listing\Column\StrategyName;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class StrategyNameUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Ui\Component\Listing\Column
 */
class StrategyNameUnitTest extends TestCase
{
    /**
     * @test
     */
    public function prepareDataSourceSuccessfully()
    {
        $strategy = $this->createMock(StrategyInterface::class);
        $strategy->expects($this->once())
            ->method('getName')
            ->willReturn('FooBar');

        $strategyManager = $this->getStrategyManager();
        $strategyManager->expects($this->any())
            ->method('has')
            ->willReturnMap([
                ['baz', false],
                ['foobar', true]
            ]);
        $strategyManager->expects($this->any())
            ->method('get')
            ->with('foobar')
            ->willReturn($strategy);

        $action = new StrategyName(
            $this->getContext(),
            $strategyManager,
            $this->getUiComponentFactory()
        );

        $action->setData([
            'name' => 'strategy'
        ]);

        $dataSource = ['foo' => 'bar'];
        $this->assertEquals($dataSource, $action->prepareDataSource($dataSource));

        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'foo' => 'bar'
                    ],
                    [
                        'strategy' => 'baz'
                    ],
                    [
                        'strategy' => 'foobar'
                    ]
                ]
            ]
        ];

        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        'foo' => 'bar'
                    ],
                    [
                        'strategy' => 'baz'
                    ],
                    [
                        'strategy' => 'FooBar'
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedDataSource, $action->prepareDataSource($dataSource));
    }

    /**
     * @return MockObject|StrategyManager
     */
    private function getStrategyManager()
    {
        return $this->getMockBuilder(StrategyManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|ContextInterface
     */
    private function getContext()
    {
        return $this->createMock(ContextInterface::class);
    }

    /**
     * @return MockObject|UiComponentFactory
     */
    private function getUiComponentFactory()
    {
        return $this->getMockBuilder(UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
