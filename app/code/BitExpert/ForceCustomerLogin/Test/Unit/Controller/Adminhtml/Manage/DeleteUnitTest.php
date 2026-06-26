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

use BitExpert\ForceCustomerLogin\Api\Repository\WhitelistRepositoryInterface;
use BitExpert\ForceCustomerLogin\Controller\Adminhtml\Manage\Delete;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ModuleCheckUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Controller
 */
class DeleteUnitTest extends TestCase
{
    /**
     * @var MockObject|WhitelistRepositoryInterface
     */
    protected $whitelist;
    /**
     * @var MockObject|Redirect
     */
    protected $redirect;
    /***
     * @var MockObject|RedirectFactory
     */
    protected $redirectFactory;
    /**
     * @var MockObject|RequestInterface
     */
    protected $request;
    /**
     * @var MockObject|ManagerInterface
     */
    protected $messageManager;
    /**
     * @var MockObject|Context
     */
    protected $context;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->whitelist = $this->createMock(WhitelistRepositoryInterface::class);
        $this->redirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getHttpResponseCode', 'setHttpResponseCode', 'renderResult'])
            ->getMock();
        $this->redirectFactory = $this->createMock(RedirectFactory::class);
        $this->redirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->redirect);
        $this->request = $this->createMock(RequestInterface::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectFactory);
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
    }

    /**
     * @test
     */
    public function successfullyDeletingWhitelistEntryReturn200Statuscode()
    {
        $this->whitelist->expects($this->once())
            ->method('deleteEntry')
            ->willReturn(true);

        $action = new Delete(
            $this->whitelist,
            $this->context
        );
        /** @var Redirect $result */
        $result = $action->execute();

        $this->assertSame($result->getHttpResponseCode(), 200);
    }

    /**
     * @test
     */
    public function failingToDeletingWhitelistEntryWillReturn500Statuscode()
    {
        $this->whitelist->expects($this->once())
            ->method('deleteEntry')
            ->willReturn(false);

        $action = new Delete(
            $this->whitelist,
            $this->context
        );
        /** @var Redirect $result */
        $result = $action->execute();

        $this->assertSame($result->getHttpResponseCode(), \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR);
    }

    /**
     * @test
     */
    public function exceptionsWillReturn500Statuscode()
    {
        $this->whitelist->expects($this->once())
            ->method('deleteEntry')
            ->willThrowException(new \RuntimeException());

        $action = new Delete(
            $this->whitelist,
            $this->context
        );
        /** @var Redirect $result */
        $result = $action->execute();

        $this->assertSame($result->getHttpResponseCode(), \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR);
    }
}
