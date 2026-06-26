<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Ui\Component\Listing\Column;

function __($value)
{
    return $value;
}

namespace BitExpert\ForceCustomerLogin\Test\Unit\Ui\Component\Listing\Column;

use BitExpert\ForceCustomerLogin\Ui\Component\Listing\Column\StoreName;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class StoreNameUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Ui\Component\Listing\Column
 */
class StoreNameUnitTest extends TestCase
{
    /**
     * @test
     */
    public function prepareDataSourceSuccessfully()
    {
        $storeId = 42;

        $globalStore = $this->createMock(StoreInterface::class);
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $store->expects($this->once())
            ->method('getName')
            ->willReturn('foobar');

        $storeManager = $this->getStoreManager();
        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturnMap([
                [0, $globalStore],
                [$storeId, $store]
            ]);


        $action = new StoreName(
            $this->getContext(),
            $storeManager,
            $this->getUiComponentFactory()
        );

        $action->setData([
            'name' => 'store'
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
                        'store_id' => '0'
                    ],
                    [
                        'store_id' => '42'
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
                        'store_id' => '0',
                        'store' => 'All Stores'
                    ],
                    [
                        'store_id' => '42',
                        'store' => 'foobar'
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedDataSource, $action->prepareDataSource($dataSource));
    }

    /**
     * @return MockObject|StoreManager
     */
    private function getStoreManager()
    {
        return $this->createMock(StoreManager::class);
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
