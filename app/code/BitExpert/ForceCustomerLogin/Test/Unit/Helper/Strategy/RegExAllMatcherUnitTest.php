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

use BitExpert\ForceCustomerLogin\Helper\Strategy\RegExAllMatcher;
use BitExpert\ForceCustomerLogin\Model\WhitelistEntry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RegExAllMatcherUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Helper\Strategy
 */
class RegExAllMatcherUnitTest extends TestCase
{
    /**
     * @test
     */
    public function matchStaticRulesCorrectly()
    {
        $matcher = new RegExAllMatcher('foobar');

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
        $this->assertFalse($matcher->isMatch('', $rule));
        $this->assertFalse($matcher->isMatch('/', $rule));
        $this->assertTrue($matcher->isMatch('/foobar', $rule));
        $this->assertTrue($matcher->isMatch('/foobar/', $rule));
        $this->assertTrue($matcher->isMatch('/foobar/baz', $rule));
        $this->assertTrue($matcher->isMatch('/foobar/baz/', $rule));
        // without rewrite
        $this->assertFalse($matcher->isMatch('/index.php', $rule));
        $this->assertFalse($matcher->isMatch('/index.php/', $rule));
        $this->assertTrue($matcher->isMatch('/index.php/foobar', $rule));
        $this->assertTrue($matcher->isMatch('/index.php/foobar/', $rule));
        $this->assertTrue($matcher->isMatch('/index.php/foobar/baz', $rule));
        $this->assertTrue($matcher->isMatch('/index.php/foobar/baz/', $rule));
    }

    /**
     * @test
     */
    public function matchCatchAllRuleCorrectly()
    {
        $matcher = new RegExAllMatcher('foobar');

        /** @var $rule MockObject|WhitelistEntry */
        $rule = $this->getMockBuilder(WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rule->expects($this->any())
            ->method('getUrlRule')
            ->willReturn('/?');

        /**
         * Rule: /?
         */
        // simple
        $this->assertTrue($matcher->isMatch('', $rule));
        $this->assertTrue($matcher->isMatch('/', $rule));
        // subpage
        $this->assertTrue($matcher->isMatch('/foobar/baz', $rule));
        $this->assertTrue($matcher->isMatch('/foobar/baz/', $rule));
    }

    /**
     * @test
     */
    public function matchCatchAllWithLineEndIdentifierRuleCorrectly()
    {
        $matcher = new RegExAllMatcher('foobar');

        /** @var $rule MockObject|WhitelistEntry */
        $rule = $this->getMockBuilder(WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rule->expects($this->any())
            ->method('getUrlRule')
            ->willReturn('/?$');

        /**
         * Rule: /?$
         */
        // simple
        $this->assertTrue($matcher->isMatch('', $rule));
        $this->assertTrue($matcher->isMatch('/', $rule));
        // subpage
        $this->assertTrue($matcher->isMatch('/foobar/baz', $rule));
        $this->assertTrue($matcher->isMatch('/foobar/baz/', $rule));
    }

    /**
     * @test
     */
    public function matchHomepageRuleCorrectly()
    {
        $matcher = new RegExAllMatcher('foobar');

        /** @var $rule MockObject|WhitelistEntry */
        $rule = $this->getMockBuilder(WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rule->expects($this->any())
            ->method('getUrlRule')
            ->willReturn('^/?$');

        /**
         * Rule: ^/?$
         */
        // simple
        $this->assertTrue($matcher->isMatch('', $rule));
        $this->assertTrue($matcher->isMatch('/', $rule));
        // subpage
        $this->assertFalse($matcher->isMatch('/foobar/baz', $rule));
        $this->assertFalse($matcher->isMatch('/foobar/baz/', $rule));
    }

    /**
     * @test
     */
    public function matchPartialPathCorrectly()
    {
        $matcher = new RegExAllMatcher('foobarPost');

        /** @var $rule MockObject|WhitelistEntry */
        $rule = $this->getMockBuilder(WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rule->expects($this->any())
            ->method('getUrlRule')
            ->willReturn('/foobarPost');

        /**
         * Rule: ^/?$
         */
        // simple
        $this->assertFalse($matcher->isMatch('', $rule));
        $this->assertFalse($matcher->isMatch('/', $rule));
        // subpage
        $this->assertFalse($matcher->isMatch('/foobar', $rule));
        $this->assertFalse($matcher->isMatch('/foobar/', $rule));
        $this->assertTrue($matcher->isMatch('/foobarPost', $rule));
        $this->assertTrue($matcher->isMatch('/foobarPost/', $rule));
        $this->assertTrue($matcher->isMatch('/foobarpost', $rule));
        $this->assertTrue($matcher->isMatch('/foobarpost/', $rule));
        $this->assertFalse($matcher->isMatch('/foobar/baz', $rule));
        $this->assertFalse($matcher->isMatch('/foobar/baz/', $rule));
    }
}
