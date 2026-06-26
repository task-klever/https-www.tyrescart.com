<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Test\Unit\Validator;

use BitExpert\ForceCustomerLogin\Validator\WhitelistEntry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class WhitelistEntryUnitTest
 *
 * @package BitExpert\ForceCustomerLogin\Test\Unit\Validator
 */
class WhitelistEntryUnitTest extends TestCase
{
    /**
     * @test
     */
    public function validationFailsDueToLabelTooShort()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Label value is too short.');

        $entity = $this->getWhitelistEntry();
        $entity->expects($this->any())
            ->method('getLabel')
            ->willReturn('');

        $validator = new WhitelistEntry();
        $validator->validate($entity);
    }

    /**
     * @test
     */
    public function validationFailsDueToLabelTooLong()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Label value is too long.');

        $entity = $this->getWhitelistEntry();
        $entity->expects($this->any())
            ->method('getLabel')
            ->willReturn(str_repeat('.', 256));

        $validator = new WhitelistEntry();
        $validator->validate($entity);
    }

    /**
     * @test
     */
    public function validationFailsDueToUrlRuleTooShort()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Url Rule value is too short.');

        $entity = $this->getWhitelistEntry();
        $entity->expects($this->any())
            ->method('getLabel')
            ->willReturn('foo');
        $entity->expects($this->any())
            ->method('getUrlRule')
            ->willReturn('');

        $validator = new WhitelistEntry();
        $validator->validate($entity);
    }

    /**
     * @test
     */
    public function validationFailsDueToUrlRuleTooLong()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Url Rule value is too long.');

        $entity = $this->getWhitelistEntry();
        $entity->expects($this->any())
            ->method('getLabel')
            ->willReturn('foo');
        $entity->expects($this->any())
            ->method('getUrlRule')
            ->willReturn(str_repeat('.', 256));

        $validator = new WhitelistEntry();
        $validator->validate($entity);
    }

    /**
     * @test
     */
    public function validationFailsDueToStrategyTooShort()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Strategy value is too short.');

        $entity = $this->getWhitelistEntry();
        $entity->expects($this->any())
            ->method('getLabel')
            ->willReturn('foo');
        $entity->expects($this->any())
            ->method('getUrlRule')
            ->willReturn('foo');
        $entity->expects($this->any())
            ->method('getStrategy')
            ->willReturn('');

        $validator = new WhitelistEntry();
        $validator->validate($entity);
    }

    /**
     * @test
     */
    public function validationFailsDueToStrategyTooLong()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Strategy value is too long.');

        $entity = $this->getWhitelistEntry();
        $entity->expects($this->any())
            ->method('getLabel')
            ->willReturn('foo');
        $entity->expects($this->any())
            ->method('getUrlRule')
            ->willReturn('foo');
        $entity->expects($this->any())
            ->method('getStrategy')
            ->willReturn(str_repeat('.', 256));

        $validator = new WhitelistEntry();
        $validator->validate($entity);
    }

    /**
     * @test
     */
    public function validationFailsDueToEditableFalseType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Editable is no boolean value.');

        $entity = $this->getWhitelistEntry();
        $entity->expects($this->any())
            ->method('getLabel')
            ->willReturn('foo');
        $entity->expects($this->any())
            ->method('getUrlRule')
            ->willReturn('foo');
        $entity->expects($this->any())
            ->method('getStrategy')
            ->willReturn('foo');
        $entity->expects($this->any())
            ->method('getEditable')
            ->willReturn('foo');

        $validator = new WhitelistEntry();
        $validator->validate($entity);
    }

    /**
     * @test
     */
    public function validationSucceeds()
    {
        $entity = $this->getWhitelistEntry();
        $entity->expects($this->any())
            ->method('getLabel')
            ->willReturn('foo');
        $entity->expects($this->any())
            ->method('getUrlRule')
            ->willReturn('foo');
        $entity->expects($this->any())
            ->method('getStrategy')
            ->willReturn('foo');
        $entity->expects($this->any())
            ->method('getEditable')
            ->willReturn(false);

        $validator = new WhitelistEntry();
        $this->assertTrue($validator->validate($entity));
    }

    /**
     * @return MockObject|\BitExpert\ForceCustomerLogin\Model\WhitelistEntry
     */
    private function getWhitelistEntry()
    {
        return $this->getMockBuilder(\BitExpert\ForceCustomerLogin\Model\WhitelistEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
