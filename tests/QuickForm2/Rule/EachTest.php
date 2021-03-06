<?php
/**
 * Unit tests for HTML_QuickForm2 package
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2014, Alexey Borzov <avb@php.net>,
 *                          Bertrand Mansion <golgote@mamasam.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(dirname(__FILE__))) . '/TestHelper.php';

/**
 * Unit test for HTML_QuickForm2_Rule_Each class
 */
class HTML_QuickForm2_Rule_EachTest extends PHPUnit\Framework\TestCase
{
    public function testTemplateRuleNeeded()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Container')
        ->setMethods(array('getType', 'setValue', '__toString'))
        ->getMock();
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $each = new HTML_QuickForm2_Rule_Each($mockEl);
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $each2 = new HTML_QuickForm2_Rule_Each($mockEl, '', 'A rule?');
    }

    public function testCannotUseRequiredAsTemplate()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Container')
        ->setMethods(array('getType', 'setValue', '__toString'))
        ->getMock();
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $each = new HTML_QuickForm2_Rule_Each($mockEl, 'an error', $mockEl->createRule('required', 'an error'));
    }

    public function testCanOnlyValidateContainers()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->setMethods(array('getType','getRawValue', 'setValue', '__toString'))
        ->getMock();
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $each = new HTML_QuickForm2_Rule_Each(
            $mockEl, '', $mockEl->createRule('empty')
        );
    }

    public function testValidatesWithTemplateRule()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
        ->setMethods(array('getType', 'setValue', '__toString'))
        ->getMock();
        $foo = $mockContainer->addElement('text', 'foo')->setValue('');
        $bar = $mockContainer->addElement('text', 'bar')->setValue('I am not empty');
        $baz = $mockContainer->addElement('text', 'baz')->setValue('');

        $each = new HTML_QuickForm2_Rule_Each(
            $mockContainer, 'an error', $mockContainer->createRule('empty')
        );
        $this->assertFalse($each->validate());

        $mockContainer->removeChild($bar);
        $this->assertTrue($each->validate());
    }

    public function testSetsErrorOnContainer()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
        ->setMethods(array('getType', 'setValue', '__toString'))
        ->getMock();
        $foo = $mockContainer->addElement('text', 'foo')->setValue('');
        $bar = $mockContainer->addElement('text', 'bar')->setValue('I am not empty');

        $each = new HTML_QuickForm2_Rule_Each(
            $mockContainer, 'Real error', $mockContainer->createRule('empty', 'Template error')
        );
        $this->assertFalse($each->validate());
        $this->assertEquals('Real error', $mockContainer->getError());
        $this->assertEquals('', $bar->getError());
    }

    public function testChainedRulesAreIgnored()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
        ->setMethods(array('getType', 'setValue', '__toString'))
        ->getMock();

        $foo = $mockContainer->addElement('text', 'foo')->setValue('');
        $ruleIgnored = $this->getMockBuilder('HTML_QuickForm2_Rule')
        ->setMethods( array('validateOwner'))
        ->setConstructorArgs(array($foo))
        ->getMock();
        $ruleIgnored->expects($this->never())->method('validateOwner');

        $each = new HTML_QuickForm2_Rule_Each(
            $mockContainer, 'an error', $mockContainer->createRule('empty')
                                                      ->and_($ruleIgnored)
        );
        $this->assertTrue($each->validate());
    }

    public function testValidateNestedContainer()
    {
        $mockOuter = $this->getMockBuilder('HTML_QuickForm2_Container')
        ->setMethods(array('getType', 'setValue', '__toString'))
        ->getMock();
        $mockInner = $this->getMockBuilder('HTML_QuickForm2_Container')
        ->setMethods(array('getType', 'setValue', '__toString'))
        ->getMock();
        $foo = $mockOuter->addElement('text', 'foo')->setValue('');
        $bar = $mockInner->addElement('text', 'bar')->setValue('not empty');
        $mockOuter->appendChild($mockInner);

        $each = new HTML_QuickForm2_Rule_Each(
            $mockOuter, 'Real error', $mockOuter->createRule('empty')
        );
        $this->assertFalse($each->validate());

        $bar->setValue('');
        $this->assertTrue($each->validate());
    }

    public function testIgnoresStaticServerSide()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
        ->setMethods(array('getType', 'setValue', '__toString'))
        ->getMock();
        $mockContainer->addElement('static', 'noValidateServer');

        $rule = $this->getMockBuilder('HTML_QuickForm2_Rule')
        ->setMethods( array('validateOwner'))
        ->setConstructorArgs(array($mockContainer, 'a message'))
        ->getMock();
        $rule->expects($this->any())->method('validateOwner')
             ->will($this->returnValue(false));

        $each = new HTML_QuickForm2_Rule_Each($mockContainer, 'an error', $rule);
        $this->assertTrue($each->validate());
    }

    public function testIgnoresStaticClientSide()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
        ->setMethods(array('getType', 'setValue', '__toString'))
        ->getMock();
        $mockContainer->addElement('static', 'noValidateClient');

        $rule = $this->getMockBuilder('HTML_QuickForm2_Rule')
        ->setMethods( array('validateOwner', 'getJavascriptCallback'))
        ->setConstructorArgs(array($mockContainer, 'a message'))
        ->getMock();
        $rule->expects($this->any())->method('getJavascriptCallback')
             ->will($this->returnValue('staticCallback'));

        $each = new HTML_QuickForm2_Rule_Each($mockContainer, 'an error', $rule);
        $this->assertNotContains('staticCallback', $each->getJavascript());
    }

    public function testValidationTriggers()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
        ->setMethods(array('getType', 'setValue', '__toString'))
        ->getMock();
        $foo = $mockContainer->addElement('text', 'foo', array('id' => 'foo'));
        $bar = $mockContainer->addElement('text', 'bar', array('id' => 'bar'));

        $rule = $this->getMockBuilder('HTML_QuickForm2_Rule')
        ->setMethods( array('validateOwner', 'getJavascriptCallback'))
        ->setConstructorArgs(array($mockContainer, 'a message'))
        ->getMock();
        $rule->expects($this->any())->method('getJavascriptCallback')
             ->will($this->returnValue('a callback'));
        $each = new HTML_QuickForm2_Rule_Each($mockContainer, 'an error', $rule);
        $this->assertContains('["foo","bar"]', $each->getJavascript());
    }
}
?>