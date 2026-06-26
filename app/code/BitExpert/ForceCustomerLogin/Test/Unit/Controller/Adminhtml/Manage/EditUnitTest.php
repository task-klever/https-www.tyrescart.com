<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Test\Unit\Controller\Adminhtml\Manage;

use BitExpert\ForceCustomerLogin\Controller\Adminhtml\Manage\Edit;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ModuleCheckUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Controller
 */
class EditUnitTest extends TestCase
{
    /**
     * @test
     */
    public function executeSuccessfully()
    {
        $view = $this->createMock(ViewInterface::class);
        $view->expects($this->once())
            ->method('loadLayout');
        $view->expects($this->once())
            ->method('renderLayout');

        $context = $this->getContext();
        $context->expects($this->atLeastOnce())
            ->method('getView')
            ->willReturn($view);

        $action = new Edit(
            $context
        );
        $action->execute();
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
}
