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
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(__FILE__)) . '/TestHelper.php';

/**
 * Unit test for HTML_QuickForm2_Factory class
 */
class HTML_QuickForm2_FactoryTest extends PHPUnit\Framework\TestCase
{
    protected $phpError;
    protected $errorHandler;

    protected $nodeAbstractMethods = array(
        'updateValue', 'getId', 'getName', 'getType', 'getRawValue', 'setId',
        'setName', 'setValue', '__toString', 'getJavascriptValue',
        'getJavascriptTriggers', 'render'
    );

    protected function setUp()
    {
        $this->phpError     = null;
        $this->errorHandler = false;
    }

    protected function tearDown()
    {
        if ($this->errorHandler) {
            restore_error_handler();
        }
    }

    protected function setErrorHandler()
    {
        set_error_handler(array($this, 'handleError'));
        $this->errorHandler = true;
    }

    public function handleError($errno, $errstr)
    {
        $this->phpError = $errstr;
    }

    public function testNotRegisteredElement()
    {
        $this->assertFalse(HTML_QuickForm2_Factory::isElementRegistered('foo_' . mt_rand()));
    }

    public function testElementTypeCaseInsensitive()
    {
        HTML_QuickForm2_Factory::registerElement('fOo', 'Classname');
        $this->assertTrue(HTML_QuickForm2_Factory::isElementRegistered('foo'));
        $this->assertTrue(HTML_QuickForm2_Factory::isElementRegistered('FOO'));
    }

    public function testCreateNotRegisteredElement()
    {
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $el = HTML_QuickForm2_Factory::createElement('foo2');
    }

    public function testCreateElementNonExistingClass()
    {
        HTML_QuickForm2_Factory::registerElement('foo3', 'NonexistentClass');
        $this->expectException('HTML_QuickForm2_Exception_NotFound');
        $this->setErrorHandler();
        $el = HTML_QuickForm2_Factory::createElement('foo3');
    }

    public function testCreateElementNonExistingFile()
    {
        HTML_QuickForm2_Factory::registerElement('foo4', 'NonexistentClass', 'NonexistentFile.php');
        $this->expectException('HTML_QuickForm2_Exception_NotFound');
        $this->setErrorHandler();
        $el = HTML_QuickForm2_Factory::createElement('foo4');
    }

    public function testCreateElementInvalidFile()
    {
        HTML_QuickForm2_Factory::registerElement('foo5', 'NonexistentClass', dirname(__FILE__) . '/_files/InvalidFile.php');
        $this->expectException('HTML_QuickForm2_Exception_NotFound');
        $el = HTML_QuickForm2_Factory::createElement('foo5');
    }

    public function testCreateElementValid()
    {
        HTML_QuickForm2_Factory::registerElement('fakeelement', 'FakeElement', dirname(__FILE__) . '/_files/FakeElement.php');
        /* @var $el FakeElement */
        $el = HTML_QuickForm2_Factory::createElement(
            'fakeelement',
                'fake',
            'attributes',
            array('options' => '', 'label' => 'fake label')
        );
        $this->assertInstanceOf('FakeElement', $el);
        $this->assertEquals('fake', $el->name);
        $this->assertEquals(array('options' => '', 'label' => 'fake label'), $el->data);
        $this->assertEquals('attributes', $el->attributes);
    }

    public function testNotRegisteredRule()
    {
        $this->assertFalse(HTML_QuickForm2_Factory::isRuleRegistered('foo_' . mt_rand()));
    }

    public function testRuleNameCaseInsensitive()
    {
        HTML_QuickForm2_Factory::registerRule('fOo', 'RuleClassname');
        $this->assertTrue(HTML_QuickForm2_Factory::isRuleRegistered('FOO'));
        $this->assertTrue(HTML_QuickForm2_Factory::isRuleRegistered('foo'));
    }

    public function testCreateNotRegisteredRule()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->setMethods($this->nodeAbstractMethods)
        ->getMock();
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $rule = HTML_QuickForm2_Factory::createRule('foo2', $mockNode);
    }

    public function testCreateRuleNonExistingClass()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->setMethods($this->nodeAbstractMethods)
        ->getMock();
        HTML_QuickForm2_Factory::registerRule('foo3', 'NonexistentClass');
        $this->expectException('HTML_QuickForm2_Exception_NotFound');
        $this->setErrorHandler();
        $rule = HTML_QuickForm2_Factory::createRule('foo3', $mockNode);
    }

    public function testCreateRuleNonExistingFile()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->setMethods($this->nodeAbstractMethods)
        ->getMock();
        HTML_QuickForm2_Factory::registerRule('foo4', 'NonexistentClass', 'NonexistentFile.php');
        $this->expectException('HTML_QuickForm2_Exception_NotFound');
        $this->setErrorHandler();
        $rule = HTML_QuickForm2_Factory::createRule('foo4', $mockNode);
    }

    public function testCreateRuleInvalidFile()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->setMethods($this->nodeAbstractMethods)
        ->getMock();
        HTML_QuickForm2_Factory::registerRule('foo5', 'NonexistentClass', dirname(__FILE__) . '/_files/InvalidFile.php');
        $this->expectException('HTML_QuickForm2_Exception_NotFound');
        $rule = HTML_QuickForm2_Factory::createRule('foo5', $mockNode);
    }

    public function testCreateRuleValid()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->setMethods($this->nodeAbstractMethods)
        ->getMock();
        HTML_QuickForm2_Factory::registerRule(
            'fakerule',
            'FakeRule',
            dirname(__FILE__) . '/_files/FakeRule.php'
        );
        /* @var $rule FakeRule */
        $rule = HTML_QuickForm2_Factory::createRule(
            'fakerule',
            $mockNode,
            'An error message',
            'Some options'
        );
        $this->assertInstanceOf('FakeRule', $rule);
        $this->assertSame($mockNode, $rule->owner);
        $this->assertEquals('An error message', $rule->getMessage());
        $this->assertEquals('Some options', $rule->getConfig());
    }
}
