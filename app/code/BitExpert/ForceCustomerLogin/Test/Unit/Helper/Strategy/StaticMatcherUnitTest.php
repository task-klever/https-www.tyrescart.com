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

use BitExpert\ForceCustomerLogin\Helper\Strategy\StaticMatcher;
use BitExpert\ForceCustomerLogin\Model\WhitelistEntry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class StaticMatcherUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Helper\Strategy
 */
class StaticMatcherUnitTest extends TestCase
{
    /**
     * @test
     */
    public function matchStaticRulesCorrectly()
    {
        $matcher = new StaticMatcher('foobar');

        $this->assertEquals('foobar', $matcher->getName());

        /** @var $rule MockObject|WhitelistEntry */
        $rule = $this->getMockBuilder(WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rule->expects($this->any())
            ->method('getUrlRule')
            ->willReturn('/foobar');

        /**
         * Rule: /foobar
         */
        // simple
        $this->assertTrue($matcher->isMatch('/foobar', $rule));
        $this->assertTrue($matcher->isMatch('/foobar/', $rule));
        $this->assertFalse($matcher->isMatch('/bar', $rule));
        $this->assertFalse($matcher->isMatch('/bar/', $rule));
        // without rewrite
        $this->assertTrue($matcher->isMatch('/index.php/foobar', $rule));
        $this->assertTrue($matcher->isMatch('/index.php/foobar/', $rule));
        $this->assertFalse($matcher->isMatch('/index.php/bar', $rule));
        $this->assertFalse($matcher->isMatch('/index.php/bar/', $rule));
    }
}
