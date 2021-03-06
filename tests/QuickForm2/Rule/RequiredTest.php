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
 * Unit test for HTML_QuickForm2_Rule_Required class
 */
class HTML_QuickForm2_Rule_RequiredTest extends PHPUnit\Framework\TestCase
{
    protected $nodeAbstractMethods = array(
        'updateValue', 'getId', 'getName', 'getType', 'getRawValue', 'setId',
        'setName', 'setValue', '__toString', 'getJavascriptValue',
        'getJavascriptTriggers', 'render'
    );

    public function testMakesElementRequired()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->setMethods($this->nodeAbstractMethods)
        ->getMock();
        $mockNode->addRule(new HTML_QuickForm2_Rule_Required($mockNode, 'element is required'));
        $this->assertTrue($mockNode->isRequired());
    }

    public function testMustBeFirstInChain()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->setMethods($this->nodeAbstractMethods)
        ->getMock();

        $rule = $mockNode->addRule(
            $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(array('validateOwner'))
            ->setConstructorArgs(array($mockNode, 'some message'))
            ->getMock()
        );
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $rule->and_(new HTML_QuickForm2_Rule_Required($mockNode, 'element is required'));
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $rule->or_(new HTML_QuickForm2_Rule_Required($mockNode, 'element is required'));
    }

    public function testCannotAppendWithOr_()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->setMethods($this->nodeAbstractMethods)
        ->getMock();
        $required = new HTML_QuickForm2_Rule_Required($mockNode, 'element is required');
        $this->expectException('HTML_QuickForm2_Exception');
        $required->or_($this->getMockBuilder('HTML_QuickForm2_Rule')
        ->setMethods(array('validateOwner'))
        ->setConstructorArgs(array($mockNode, 'some message'))
        ->getMock());
    }

    /**
     * @link http://pear.php.net/bugs/18133
     * @expectedException HTML_QuickForm2_Exception_InvalidArgument
     */
    public function testCannotHaveEmptyMessage()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->setMethods($this->nodeAbstractMethods)
        ->getMock();
        $required = new HTML_QuickForm2_Rule_Required($mockNode);
    }
}
