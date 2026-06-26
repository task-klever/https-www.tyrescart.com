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

use BitExpert\ForceCustomerLogin\Ui\Component\Listing\Column\DeleteAction;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class DeleteActionUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Ui\Component\Listing\Column
 */
class DeleteActionUnitTest extends TestCase
{
    /**
     * @test
     */
    public function prepareDataSourceSuccessfully()
    {
        $url = $this->getUrl();
        $url->expects($this->once())
            ->method('getUrl')
            ->with(
                'viewurlpath',
                [
                    'id' => '1'
                ]
            )
            ->willReturn('some-url');

        $action = new DeleteAction(
            $this->getContext(),
            $this->getUiComponentFactory(),
            $url
        );

        $action->setData([
            'config' => [
                'idFieldName' => 'whitelist_entry_id',
                'viewUrlPath' => 'viewurlpath',
                'label' => 'some-label'
            ],
            'name' => 'delete'
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
                        'whitelist_entry_id' => '1',
                        'editable' => '1'
                    ],
                    [
                        'whitelist_entry_id' => '2',
                        'editable' => '0'
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
                        'whitelist_entry_id' => '1',
                        'editable' => '1',
                        'delete' => [
                            'edit' => [
                                'href' => 'some-url',
                                'label' => 'some-label'
                            ]
                        ]
                    ],
                    [
                        'whitelist_entry_id' => '2',
                        'editable' => '0'
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedDataSource, $action->prepareDataSource($dataSource));
    }

    /**
     * @return MockObject|UrlInterface
     */
    private function getUrl()
    {
        return $this->createMock(UrlInterface::class);
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
