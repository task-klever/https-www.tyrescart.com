<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Test\Unit\Controller;

use BitExpert\ForceCustomerLogin\Api\Controller\LoginCheckInterface;
use BitExpert\ForceCustomerLogin\Api\Repository\WhitelistRepositoryInterface;
use BitExpert\ForceCustomerLogin\Controller\LoginCheck;
use BitExpert\ForceCustomerLogin\Controller\ModuleCheck;
use BitExpert\ForceCustomerLogin\Controller\PasswordResetHelper;
use BitExpert\ForceCustomerLogin\Helper\Strategy\StrategyInterface;
use BitExpert\ForceCustomerLogin\Helper\Strategy\StrategyManager;
use BitExpert\ForceCustomerLogin\Model\ResourceModel\WhitelistEntry\Collection;
use BitExpert\ForceCustomerLogin\Model\Session;
use BitExpert\ForceCustomerLogin\Model\WhitelistEntry;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class LoginCheckUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Controller
 */
class LoginCheckUnitTest extends TestCase
{
    /**
     * @test
     */
    public function testClassExists()
    {
        $this->assertTrue(class_exists(LoginCheck::class));
    }

    /**
     * @test
     * @depends testClassExists
     */
    public function testConstructor()
    {
        $loginCheck = new LoginCheck(
            $this->getContext(),
            $this->getCustomerSession(),
            $this->getSession(),
            $this->getStoreManager(),
            $this->getScopeConfig(),
            $this->getWhitelistRepository(),
            $this->getStrategyManager(),
            $this->getModuleCheck(),
            $this->getResponseHttp(),
            $this->getPasswordResetHelper()
        );

        // check if mandatory interfaces are implemented
        $classInterfaces = class_implements($loginCheck);
        $this->assertContains(LoginCheckInterface::class, $classInterfaces);
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
     * @return MockObject|\Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        return $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|\BitExpert\ForceCustomerLogin\Model\Session
     */
    private function getSession()
    {
        return $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setAfterLoginReferer',
                'getData'
            ])
            ->getMock();
    }

    /**
     * @return MockObject|StoreManagerInterface
     */
    private function getStoreManager()
    {
        return $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|ScopeConfigInterface
     */
    private function getScopeConfig()
    {
        return $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|WhitelistRepositoryInterface
     */
    private function getWhitelistRepository()
    {
        return $this->createMock(WhitelistRepositoryInterface::class);
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
     * @return MockObject|ModuleCheck
     */
    private function getModuleCheck()
    {
        return $this->getMockBuilder(ModuleCheck::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|\Magento\Framework\App\Response\Http
     */
    private function getResponseHttp()
    {
        return $this->getMockBuilder(ResponseHttp::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|RequestInterface
     */
    private function getRequest()
    {
        return $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getModuleName',
                'setModuleName',
                'getActionName',
                'setActionName',
                'getParam',
                'setParams',
                'getParams',
                'getCookie',
                'isSecure',
                'isPost'
            ])
            ->getMock();
    }

    /**
     * @return MockObject|Http
     */
    private function getRequestObject()
    {
        return $this->createMock(RequestHttp::class);
    }

    /**
     * @return MockObject|UrlInterface
     */
    private function getUrl()
    {
        return $this->createMock(UrlInterface::class);
    }

    /**
     * @return MockObject|ResponseInterface
     */
    private function getResponse()
    {
        return $this->createMock(ResponseInterface::class);
    }

    /**
     * @return MockObject|RedirectInterface
     */
    private function getRedirect()
    {
        return $this->createMock(RedirectInterface::class);
    }

    /**
     * @return MockObject|PasswordResetHelper
     */
    private function getPasswordResetHelper()
    {
        return $this->getMockBuilder(PasswordResetHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Run test with url equals target, so no redirecting is happening.
     *
     * @test
     * @depends testConstructor
     */
    public function skipMatchingWhenModuleIsDisabled()
    {
        $moduleCheck = $this->getModuleCheck();
        $moduleCheck->expects($this->once())
            ->method('isModuleEnabled')
            ->willReturn(false);

        $context = $this->getContext();

        $loginCheck = new LoginCheck(
            $context,
            $this->getCustomerSession(),
            $this->getSession(),
            $this->getStoreManager(),
            $this->getScopeConfig(),
            $this->getWhitelistRepository(),
            $this->getStrategyManager(),
            $moduleCheck,
            $this->getResponseHttp(),
            $this->getPasswordResetHelper()
        );

        $loginCheck->execute();
    }

    /**
     * Run test with existing customer session, so no redirecting is happening.
     *
     * @test
     * @depends testConstructor
     */
    public function skipMatchingWhenCustomerSessionIsActive()
    {
        $moduleCheck = $this->getModuleCheck();
        $moduleCheck->expects($this->once())
            ->method('isModuleEnabled')
            ->willReturn(true);

        $customerSession = $this->getCustomerSession();
        $customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $context = $this->getContext();

        $loginCheck = new LoginCheck(
            $context,
            $customerSession,
            $this->getSession(),
            $this->getStoreManager(),
            $this->getScopeConfig(),
            $this->getWhitelistRepository(),
            $this->getStrategyManager(),
            $moduleCheck,
            $this->getResponseHttp(),
            $this->getPasswordResetHelper()
        );

        $loginCheck->execute();
    }

    /**
     * Run test with a Magento Admin logging in as customer, so no redirecting is happening.
     *
     * @test
     * @depends testConstructor
     */
    public function skipMatchingWhenCustomerLoginViaMagentoAdminHappens()
    {
        $moduleCheck = $this->getModuleCheck();
        $moduleCheck->expects($this->once())
            ->method('isModuleEnabled')
            ->willReturn(true);

        $session = $this->getSession();
        $session->expects($this->once())
            ->method('getData')
            ->willReturn(['after_login_referer' => 'loginascustomer']);

        $context = $this->getContext();

        $loginCheck = new LoginCheck(
            $context,
            $this->getCustomerSession(),
            $session,
            $this->getStoreManager(),
            $this->getScopeConfig(),
            $this->getWhitelistRepository(),
            $this->getStrategyManager(),
            $moduleCheck,
            $this->getResponseHttp(),
            $this->getPasswordResetHelper()
        );

        $loginCheck->execute();
    }

    /**
     * Run test with url equals target, so no redirecting is happening.
     *
     * @test
     * @depends testConstructor
     */
    public function urlMatchesTargetUrlExactlyAndNoRedirectIsForced()
    {
        $urlString = 'http://example.tld/customer/account/login';
        $targetUrl = '/customer/account/login';

        // --- Scope Config
        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                LoginCheckInterface::MODULE_CONFIG_TARGET,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($targetUrl);

        // --- StoreManager
        $storeManager = $this->getStoreManager();
        $storeManager->expects($this->never())
            ->method('getStore');

        // --- Context
        $url = $this->getUrl();
        $url->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn($urlString);

        $response = $this->getResponse();
        $redirect = $this->getRedirect();

        $context = $this->getContext();
        $context->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);

        // --- Response
        $responseHttp = $this->getResponseHttp();
        $responseHttp->expects($this->never())
            ->method('setNoCacheHeaders');
        $responseHttp->expects($this->never())
            ->method('setRedirect');
        $responseHttp->expects($this->never())
            ->method('sendResponse');

        // --- Whitelist Entries
        $whitelistRepository = $this->getWhitelistRepository();
        $whitelistRepository->expects($this->never())
            ->method('getCollection');

        // --- Strategy
        $strategyManager = $this->getStrategyManager();
        $strategyManager->expects($this->never())
            ->method('get');

        $loginCheck = new LoginCheck(
            $context,
            $this->getCustomerSession(),
            $this->getSession(),
            $storeManager,
            $scopeConfig,
            $whitelistRepository,
            $strategyManager,
            $this->getModuleCheck(),
            $responseHttp,
            $this->getPasswordResetHelper()
        );

        $loginCheck->execute();
    }

    /**
     * Run test with data listed on the whitelist, so no redirecting is happening.
     *
     * @test
     * @depends testConstructor
     */
    public function ruleMatchingPositiveWithoutRedirect()
    {
        $urlString = 'http://example.tld/foo/bar';
        $targetUrl = '/customer/account/login';

        // --- Scope Config
        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                LoginCheckInterface::MODULE_CONFIG_TARGET,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($targetUrl);

        // --- StoreManager
        $storeManager = $this->getStoreManager();
        $storeManager->expects($this->never())
            ->method('getStore');

        // --- Context
        $url = $this->getUrl();
        $url->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn($urlString);

        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $context = $this->getContext();
        $context->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);
        $context->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        // --- Response
        $responseHttp = $this->getResponseHttp();
        $responseHttp->expects($this->never())
            ->method('setNoCacheHeaders');
        $responseHttp->expects($this->never())
            ->method('setRedirect');
        $responseHttp->expects($this->never())
            ->method('sendResponse');

        // --- Whitelist Entries
        $whitelistEntityOne = $this->getMockBuilder(WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistEntityOne->expects($this->once())
            ->method('getStrategy')
            ->willReturn('default');
        $whitelistCollection = $this
            ->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$whitelistEntityOne]);
        $whitelistRepository = $this->getWhitelistRepository();
        $whitelistRepository->expects($this->once())
            ->method('getCollection')
            ->willReturn($whitelistCollection);

        // --- Strategy
        $strategy = $this->createMock(StrategyInterface::class);
        $strategy->expects($this->once())
            ->method('isMatch')
            ->with('/foo/bar', $whitelistEntityOne)
            ->willReturn(true);

        $strategyManager = $this->getStrategyManager();
        $strategyManager->expects($this->once())
            ->method('get')
            ->with('default')
            ->willReturn($strategy);

        $loginCheck = new LoginCheck(
            $context,
            $this->getCustomerSession(),
            $this->getSession(),
            $storeManager,
            $scopeConfig,
            $whitelistRepository,
            $strategyManager,
            $this->getModuleCheck(),
            $responseHttp,
            $this->getPasswordResetHelper()
        );

        $loginCheck->execute();
    }

    /**
     * Run test with data not listed on the whitelist, so redirecting is forced.
     *
     * @test
     * @depends testConstructor
     */
    public function ruleMatchingFailsAndResultsInRedirect()
    {
        $urlString = 'http://example.tld/foo/bar';
        $targetUrl = '/customer/account/login';
        $expectedTargetUrl = 'http://example.tld/foo/bar/customer/account/login';

        // --- Scope Config
        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->withConsecutive(
                [LoginCheckInterface::MODULE_CONFIG_TARGET, ScopeInterface::SCOPE_STORE],
                [LoginCheckInterface::MODULE_CONFIG_FORCE_SECURE_REDIRECT, ScopeInterface::SCOPE_STORE])
            ->willReturnOnConsecutiveCalls($targetUrl, false);

        // --- StoreManager
        $store = $this->getMockBuilder(StoreInterface::class)
            ->setMethods([
                'getBaseUrl',
                'getId',
                'setId',
                'getCode',
                'setCode',
                'getName',
                'setName',
                'getWebsiteId',
                'setWebsiteId',
                'getStoreGroupId',
                'setStoreGroupId',
                'getExtensionAttributes',
                'setExtensionAttributes',
                'setIsActive',
                'getIsActive'
            ])
            ->getMock();
        $store->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_LINK, null)
            ->willReturn($urlString);
        $storeManager = $this->getStoreManager();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        // --- Context
        $url = $this->getUrl();
        $url->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn($urlString);

        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $context = $this->getContext();
        $context->expects($this->exactly(1))
            ->method('getUrl')
            ->willReturn($url);
        $context->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        // --- Response
        $responseHttp = $this->getResponseHttp();
        $responseHttp->expects($this->once())
            ->method('setNoCacheHeaders');
        $responseHttp->expects($this->once())
            ->method('setRedirect')
            ->with($expectedTargetUrl);
        $responseHttp->expects($this->once())
            ->method('sendResponse');

        // --- Request
        $request->expects($this->exactly(2))
            ->method('getParam');

        // --- Whitelist Entries
        $whitelistEntityOne = $this->getMockBuilder(WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistEntityOne->expects($this->once())
            ->method('getStrategy')
            ->willReturn('default');
        $whitelistCollection = $this
            ->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$whitelistEntityOne]);
        $whitelistRepository = $this->getWhitelistRepository();
        $whitelistRepository->expects($this->once())
            ->method('getCollection')
            ->willReturn($whitelistCollection);

        // --- Strategy
        $strategy = $this->createMock(StrategyInterface::class);
        $strategy->expects($this->once())
            ->method('isMatch')
            ->with('/foo/bar', $whitelistEntityOne)
            ->willReturn(false);

        $strategyManager = $this->getStrategyManager();
        $strategyManager->expects($this->once())
            ->method('get')
            ->with('default')
            ->willReturn($strategy);

        // -- Session
        $session = $this->getSession();
        $session->expects($this->once())
            ->method('setAfterLoginReferer')
            ->with('/foo/bar');

        $loginCheck = new LoginCheck(
            $context,
            $this->getCustomerSession(),
            $session,
            $storeManager,
            $scopeConfig,
            $whitelistRepository,
            $strategyManager,
            $this->getModuleCheck(),
            $responseHttp,
            $this->getPasswordResetHelper()
        );

        $loginCheck->execute();
    }

    /**
     * In case of a redirect set BeforeAuthUrl for further use.
     *
     * @test
     * @depends testConstructor
     */
    public function ensureSetBeforeAuthUrlBeforeRedirect()
    {
        $urlString = 'http://example.tld/foo/bar';
        $targetUrl = '/customer/account/login';
        $expectedTargetUrl = 'http://example.tld/foo/bar/customer/account/login';

        // --- Scope Config
        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->withConsecutive(
                [LoginCheckInterface::MODULE_CONFIG_TARGET, ScopeInterface::SCOPE_STORE],
                [LoginCheckInterface::MODULE_CONFIG_FORCE_SECURE_REDIRECT, ScopeInterface::SCOPE_STORE])
            ->willReturnOnConsecutiveCalls($targetUrl, true);

        // --- StoreManager
        $store = $this->getMockBuilder(StoreInterface::class)
            ->setMethods([
                'getBaseUrl',
                'getId',
                'setId',
                'getCode',
                'setCode',
                'getName',
                'setName',
                'getWebsiteId',
                'setWebsiteId',
                'getStoreGroupId',
                'setStoreGroupId',
                'getExtensionAttributes',
                'setExtensionAttributes',
                'setIsActive',
                'getIsActive'
            ])
            ->getMock();
        $store->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_LINK, true)
            ->willReturn($urlString);
        $storeManager = $this->getStoreManager();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        // --- CustomerSession
        $customerSession = $this->getCustomerSession();
        $customerSession->expects($this->once())
            ->method('setBeforeAuthUrl')
            ->willReturn(true);

        // --- Context
        $url = $this->getUrl();
        $url->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn($urlString);

        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $context = $this->getContext();
        $context->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);
        $context->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        // --- Response
        $responseHttp = $this->getResponseHttp();
        $responseHttp->expects($this->once())
            ->method('setNoCacheHeaders');
        $responseHttp->expects($this->once())
            ->method('setRedirect')
            ->with($expectedTargetUrl);
        $responseHttp->expects($this->once())
            ->method('sendResponse');

        // --- Request
        $request->expects($this->exactly(2))
            ->method('getParam');

        // --- Whitelist Entries
        $whitelistEntityOne = $this->getMockBuilder(WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistEntityOne->expects($this->once())
            ->method('getStrategy')
            ->willReturn('default');
        $whitelistCollection = $this
            ->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$whitelistEntityOne]);
        $whitelistRepository = $this->getWhitelistRepository();
        $whitelistRepository->expects($this->once())
            ->method('getCollection')
            ->willReturn($whitelistCollection);

        // --- Strategy
        $strategy = $this->createMock(StrategyInterface::class);
        $strategy->expects($this->once())
            ->method('isMatch')
            ->with('/foo/bar', $whitelistEntityOne)
            ->willReturn(false);

        $strategyManager = $this->getStrategyManager();
        $strategyManager->expects($this->once())
            ->method('get')
            ->with('default')
            ->willReturn($strategy);

        // -- Session
        $session = $this->getSession();
        $session->expects($this->once())
            ->method('setAfterLoginReferer')
            ->with('/foo/bar');

        $loginCheck = new LoginCheck(
            $context,
            $customerSession,
            $session,
            $storeManager,
            $scopeConfig,
            $whitelistRepository,
            $strategyManager,
            $this->getModuleCheck(),
            $responseHttp,
            $this->getPasswordResetHelper()
        );

        $loginCheck->execute();
    }

    /**
     * Run test with data not listed on the whitelist, so redirecting is forced.
     *
     * @test
     * @depends testConstructor
     */
    public function ruleMatchingFailsAndResultsInSecureRedirect()
    {
        $urlString = 'http://example.tld/foo/bar';
        $targetUrl = '/customer/account/login';
        $expectedTargetUrl = 'http://example.tld/foo/bar/customer/account/login';

        // --- Scope Config
        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->withConsecutive(
                [LoginCheckInterface::MODULE_CONFIG_TARGET, ScopeInterface::SCOPE_STORE],
                [LoginCheckInterface::MODULE_CONFIG_FORCE_SECURE_REDIRECT, ScopeInterface::SCOPE_STORE])
            ->willReturnOnConsecutiveCalls($targetUrl, true);

        // --- StoreManager
        $store = $this->getMockBuilder(StoreInterface::class)
            ->setMethods([
                'getBaseUrl',
                'getId',
                'setId',
                'getCode',
                'setCode',
                'getName',
                'setName',
                'getWebsiteId',
                'setWebsiteId',
                'getStoreGroupId',
                'setStoreGroupId',
                'getExtensionAttributes',
                'setExtensionAttributes',
                'setIsActive',
                'getIsActive'
            ])
            ->getMock();
        $store->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_LINK, true)
            ->willReturn($urlString);
        $storeManager = $this->getStoreManager();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        // --- Context
        $url = $this->getUrl();
        $url->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn($urlString);

        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $context = $this->getContext();
        $context->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);
        $context->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        // --- Response
        $responseHttp = $this->getResponseHttp();
        $responseHttp->expects($this->once())
            ->method('setNoCacheHeaders');
        $responseHttp->expects($this->once())
            ->method('setRedirect')
            ->with($expectedTargetUrl);
        $responseHttp->expects($this->once())
            ->method('sendResponse');

        // --- Request
        $request->expects($this->exactly(2))
            ->method('getParam');

        // --- Whitelist Entries
        $whitelistEntityOne = $this->getMockBuilder(WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistEntityOne->expects($this->once())
            ->method('getStrategy')
            ->willReturn('default');
        $whitelistCollection = $this
            ->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$whitelistEntityOne]);
        $whitelistRepository = $this->getWhitelistRepository();
        $whitelistRepository->expects($this->once())
            ->method('getCollection')
            ->willReturn($whitelistCollection);

        // --- Strategy
        $strategy = $this->createMock(StrategyInterface::class);
        $strategy->expects($this->once())
            ->method('isMatch')
            ->with('/foo/bar', $whitelistEntityOne)
            ->willReturn(false);

        $strategyManager = $this->getStrategyManager();
        $strategyManager->expects($this->once())
            ->method('get')
            ->with('default')
            ->willReturn($strategy);

        // -- Session
        $session = $this->getSession();
        $session->expects($this->once())
            ->method('setAfterLoginReferer')
            ->with('/foo/bar');

        $loginCheck = new LoginCheck(
            $context,
            $this->getCustomerSession(),
            $session,
            $storeManager,
            $scopeConfig,
            $whitelistRepository,
            $strategyManager,
            $this->getModuleCheck(),
            $responseHttp,
            $this->getPasswordResetHelper()
        );

        $loginCheck->execute();
    }

    /**
     * Run test with ajax request and rule matching fails, so redirect is happening but "after login url" is not saved.
     *
     * @test
     * @depends testConstructor
     */
    public function requestIsAjaxAndRuleMatchingFails()
    {
        $urlString = 'http://example.tld/company-module/api/endpoint';
        $targetUrl = '/customer/account/login';
        $expectedTargetUrl = 'http://example.tld/company-module/api/endpoint/customer/account/login';

        // --- Scope Config
        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->withConsecutive(
                [LoginCheckInterface::MODULE_CONFIG_TARGET, ScopeInterface::SCOPE_STORE],
                [LoginCheckInterface::MODULE_CONFIG_FORCE_SECURE_REDIRECT, ScopeInterface::SCOPE_STORE])
            ->willReturnOnConsecutiveCalls($targetUrl, false);

        // --- StoreManager
        $store = $this->getMockBuilder(StoreInterface::class)
            ->setMethods([
                'getBaseUrl',
                'getId',
                'setId',
                'getCode',
                'setCode',
                'getName',
                'setName',
                'getWebsiteId',
                'setWebsiteId',
                'getStoreGroupId',
                'setStoreGroupId',
                'getExtensionAttributes',
                'setExtensionAttributes',
                'setIsActive',
                'getIsActive'
            ])
            ->getMock();
        $store->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_LINK, null)
            ->willReturn($urlString);
        $storeManager = $this->getStoreManager();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        // --- Context
        $url = $this->getUrl();
        $url->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn($urlString);

        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $context = $this->getContext();
        $context->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);
        $context->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        // --- Response
        $responseHttp = $this->getResponseHttp();
        $responseHttp->expects($this->once())
            ->method('setNoCacheHeaders');
        $responseHttp->expects($this->once())
            ->method('setRedirect')
            ->with($expectedTargetUrl);
        $responseHttp->expects($this->once())
            ->method('sendResponse');

        // --- Request
        $request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['ajax', null, null],
                ['isAjax', null, '1']
            ]);

        // --- Whitelist Entries
        $whitelistEntityOne = $this->getMockBuilder(WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistEntityOne->expects($this->once())
            ->method('getStrategy')
            ->willReturn('default');
        $whitelistCollection = $this
            ->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$whitelistEntityOne]);
        $whitelistRepository = $this->getWhitelistRepository();
        $whitelistRepository->expects($this->once())
            ->method('getCollection')
            ->willReturn($whitelistCollection);

        // --- Strategy
        $strategy = $this->createMock(StrategyInterface::class);
        $strategy->expects($this->once())
            ->method('isMatch')
            ->with('/company-module/api/endpoint', $whitelistEntityOne)
            ->willReturn(false);

        $strategyManager = $this->getStrategyManager();
        $strategyManager->expects($this->once())
            ->method('get')
            ->with('default')
            ->willReturn($strategy);

        // -- Session
        $session = $this->getSession();
        $session->expects($this->never())
            ->method('setAfterLoginReferer');

        $loginCheck = new LoginCheck(
            $context,
            $this->getCustomerSession(),
            $session,
            $storeManager,
            $scopeConfig,
            $whitelistRepository,
            $strategyManager,
            $this->getModuleCheck(),
            $responseHttp,
            $this->getPasswordResetHelper()
        );

        $loginCheck->execute();
    }

    /**
     * Run test with default request object and with data not listed on the whitelist, so redirecting is forced and
     * "isAjax" method is hit.
     *
     * @test
     * @depends testConstructor
     */
    public function ruleMatchingFailsAjaxCheckUsesHttpObject()
    {
        $urlString = 'http://example.tld/foo/bar';
        $targetUrl = '/customer/account/login';
        $expectedTargetUrl = 'http://example.tld/foo/bar/customer/account/login';

        // --- Scope Config
        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->withConsecutive(
                [LoginCheckInterface::MODULE_CONFIG_TARGET, ScopeInterface::SCOPE_STORE],
                [LoginCheckInterface::MODULE_CONFIG_FORCE_SECURE_REDIRECT, ScopeInterface::SCOPE_STORE])
            ->willReturnOnConsecutiveCalls($targetUrl, false);

        // --- StoreManager
        $store = $this->getMockBuilder(StoreInterface::class)
            ->setMethods([
                'getBaseUrl',
                'getId',
                'setId',
                'getCode',
                'setCode',
                'getName',
                'setName',
                'getWebsiteId',
                'setWebsiteId',
                'getStoreGroupId',
                'setStoreGroupId',
                'getExtensionAttributes',
                'setExtensionAttributes',
                'setIsActive',
                'getIsActive'
            ])
            ->getMock();
        $store->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_LINK, null)
            ->willReturn($urlString);
        $storeManager = $this->getStoreManager();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        // --- Context
        $url = $this->getUrl();
        $url->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn($urlString);

        $request = $this->getRequestObject();
        $request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $context = $this->getContext();
        $context->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);
        $context->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        // --- Response
        $responseHttp = $this->getResponseHttp();
        $responseHttp->expects($this->once())
            ->method('setNoCacheHeaders');
        $responseHttp->expects($this->once())
            ->method('setRedirect')
            ->with($expectedTargetUrl);
        $responseHttp->expects($this->once())
            ->method('sendResponse');

        // --- Request
        $request->expects($this->once())
            ->method('isAjax')
            ->willReturn(false);

        // --- Whitelist Entries
        $whitelistEntityOne = $this->getMockBuilder(WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistEntityOne->expects($this->once())
            ->method('getStrategy')
            ->willReturn('default');
        $whitelistCollection = $this
            ->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$whitelistEntityOne]);
        $whitelistRepository = $this->getWhitelistRepository();
        $whitelistRepository->expects($this->once())
            ->method('getCollection')
            ->willReturn($whitelistCollection);

        // --- Strategy
        $strategy = $this->createMock(StrategyInterface::class);
        $strategy->expects($this->once())
            ->method('isMatch')
            ->with('/foo/bar', $whitelistEntityOne)
            ->willReturn(false);

        $strategyManager = $this->getStrategyManager();
        $strategyManager->expects($this->once())
            ->method('get')
            ->with('default')
            ->willReturn($strategy);

        // -- Session
        $session = $this->getSession();
        $session->expects($this->once())
            ->method('setAfterLoginReferer')
            ->with('/foo/bar');

        $loginCheck = new LoginCheck(
            $context,
            $this->getCustomerSession(),
            $session,
            $storeManager,
            $scopeConfig,
            $whitelistRepository,
            $strategyManager,
            $this->getModuleCheck(),
            $responseHttp,
            $this->getPasswordResetHelper()
        );

        $loginCheck->execute();
    }

    /**
     * Run test with default request object and with data not listed on the whitelist, so redirecting is forced and
     * "isAjax" method is hit.
     *
     * @test
     * @depends testConstructor
     */
    public function redirectMatchesReferrerUrlWithQueryParameters()
    {
        $baseUrl = 'http://example.tld';
        $referrerUrl = 'http://example.tld/foo/bar?q=apples';
        $targetUrl = '/customer/account/login';
        $expectedTargetUrl = 'http://example.tld/customer/account/login';
        $expectedAfterLoginRedirect = '/foo/bar?q=apples';

        // --- Scope Config
        $scopeConfig = $this->getScopeConfig();
        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->withConsecutive(
                [LoginCheckInterface::MODULE_CONFIG_TARGET, ScopeInterface::SCOPE_STORE],
                [LoginCheckInterface::MODULE_CONFIG_FORCE_SECURE_REDIRECT, ScopeInterface::SCOPE_STORE])
            ->willReturnOnConsecutiveCalls($targetUrl, false);

        // --- StoreManager
        $store = $this->getMockBuilder(StoreInterface::class)
            ->setMethods([
                'getBaseUrl',
                'getId',
                'setId',
                'getCode',
                'setCode',
                'getName',
                'setName',
                'getWebsiteId',
                'setWebsiteId',
                'getStoreGroupId',
                'setStoreGroupId',
                'getExtensionAttributes',
                'setExtensionAttributes',
                'setIsActive',
                'getIsActive'
            ])
            ->getMock();
        $store->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_LINK, null)
            ->willReturn($baseUrl);
        $storeManager = $this->getStoreManager();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        // --- Context
        $url = $this->getUrl();
        $url->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn($referrerUrl);

        $request = $this->getRequestObject();
        $request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $context = $this->getContext();
        $context->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);
        $context->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        // --- Response
        $responseHttp = $this->getResponseHttp();
        $responseHttp->expects($this->once())
            ->method('setNoCacheHeaders');
        $responseHttp->expects($this->once())
            ->method('setRedirect')
            ->with($expectedTargetUrl);
        $responseHttp->expects($this->once())
            ->method('sendResponse');

        // --- Request
        $request->expects($this->once())
            ->method('isAjax')
            ->willReturn(false);

        // --- Whitelist Entries
        $whitelistEntityOne = $this->getMockBuilder(WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistEntityOne->expects($this->once())
            ->method('getStrategy')
            ->willReturn('default');
        $whitelistCollection = $this
            ->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whitelistCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$whitelistEntityOne]);
        $whitelistRepository = $this->getWhitelistRepository();
        $whitelistRepository->expects($this->once())
            ->method('getCollection')
            ->willReturn($whitelistCollection);

        // --- Strategy
        $strategy = $this->createMock(StrategyInterface::class);
        $strategy->expects($this->once())
            ->method('isMatch')
            ->with('/foo/bar', $whitelistEntityOne)
            ->willReturn(false);

        $strategyManager = $this->getStrategyManager();
        $strategyManager->expects($this->once())
            ->method('get')
            ->with('default')
            ->willReturn($strategy);

        // -- Session
        $session = $this->getSession();
        $session->expects($this->once())
            ->method('setAfterLoginReferer')
            ->with($expectedAfterLoginRedirect);

        $loginCheck = new LoginCheck(
            $context,
            $this->getCustomerSession(),
            $session,
            $storeManager,
            $scopeConfig,
            $whitelistRepository,
            $strategyManager,
            $this->getModuleCheck(),
            $responseHttp,
            $this->getPasswordResetHelper()
        );

        $loginCheck->execute();
    }
}
