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
 * Unit test for HTML_QuickForm2_Rule_Length class
 */
class HTML_QuickForm2_Rule_LengthTest extends PHPUnit\Framework\TestCase
{
    public function testLimitsAreRequired()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->setMethods(array('getType','getRawValue', 'setValue', '__toString'))
        ->getMock();
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $length = new HTML_QuickForm2_Rule_Length($mockEl, 'an error');

        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $length2 = new HTML_QuickForm2_Rule_Length($mockEl, 'another error', array());
    }

    public function testScalarLengthIsPositive()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->setMethods(array('getType','getRawValue', 'setValue', '__toString'))
        ->getMock();
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $lengthZero = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', 0);
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $lengthNegative = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', -1);
    }

    public function testMinMaxLengthIsNonnegative()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->setMethods(array('getType','getRawValue', 'setValue', '__toString'))
        ->getMock();
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $lengthZeros = new HTML_QuickForm2_Rule_Length(
            $mockEl,
            'an error',
            array('min' => 0, 'max' => 0)
        );

        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $lengthNegative = new HTML_QuickForm2_Rule_Length(
            $mockEl,
            'an error',
            array('min' => -1, 'max' => 1)
        );
    }

    public function testLimitsHandling()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->setMethods(array('getType','getValue','getRawValue', 'setValue', '__toString'))
        ->getMock();
        $mockEl->expects($this->atLeastOnce())
               ->method('getValue')->will($this->returnValue('абв'));

        $length3 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', 3);
        $this->assertTrue($length3->validate());

        $length5 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', 5);
        $this->assertFalse($length5->validate());

        $length2_4 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', array('min' => 2, 'max' => 4));
        $this->assertTrue($length2_4->validate());

        $length5_6 = new HTML_QuickForm2_Rule_Length(
            $mockEl,
            'an error',
            array('min' => 5, 'max' => 6)
        );
        $this->assertFalse($length5_6->validate());

        $minLength2 = new HTML_QuickForm2_Rule_Length(
            $mockEl,
            'an error',
            array('min' => 2)
        );
        $this->assertTrue($minLength2->validate());

        $maxLength2 = new HTML_QuickForm2_Rule_Length(
            $mockEl,
            'an error',
            array('max' => 2)
        );
        $this->assertFalse($maxLength2->validate());
    }

    public function testConfigCanonicalForm()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->setMethods(array('getType','getRawValue', 'setValue', '__toString'))
        ->getMock();
        $length = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', array('min' => 4, 'max' => 2));
        $this->assertEquals(array('min' => 2, 'max' => 4), $length->getConfig());

        $length->setConfig(array(2, 4));
        $this->assertEquals(array('min' => 2, 'max' => 4), $length->getConfig());

        $length->setConfig(array('min' => 2));
        $this->assertEquals(array('min' => 2, 'max' => 0), $length->getConfig());

        $length->setConfig(array('max' => 2));
        $this->assertEquals(array('min' => 0, 'max' => 2), $length->getConfig());
    }

    public function testGlobalConfigOverrides()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->setMethods(array('getType','getRawValue', 'setValue', '__toString'))
        ->getMock();

        $scalar = new HTML_QuickForm2_Rule_Length(
            $mockEl,
            'an error',
            HTML_QuickForm2_Rule_Length::mergeConfig(3, 4)
        );
        $this->assertEquals(4, $scalar->getConfig());

        $scalar2 = new HTML_QuickForm2_Rule_Length(
            $mockEl,
            'an error',
            HTML_QuickForm2_Rule_Length::mergeConfig(array('min' => 1, 'max' => 2), 3)
        );
        $this->assertEquals(3, $scalar2->getConfig());

        $array = new HTML_QuickForm2_Rule_Length(
            $mockEl,
            'an error',
            HTML_QuickForm2_Rule_Length::mergeConfig(
                array('min' => 1, 'max' => 2),
                array('min' => 3, 'max' => 4)
            )
        );
        $this->assertEquals(array('min' => 3, 'max' => 4), $array->getConfig());

        $array2 = new HTML_QuickForm2_Rule_Length(
            $mockEl,
            'an error',
            HTML_QuickForm2_Rule_Length::mergeConfig(123, array('min' => 3, 'max' => 4))
        );
        $this->assertEquals(array('min' => 3, 'max' => 4), $array2->getConfig());
    }

    public function testConfigMerging()
    {
        $this->assertEquals(
            array('min' => 1, 'max' => 0),
            HTML_QuickForm2_Rule_Length::mergeConfig(1, array('max' => 0))
        );

        $this->assertEquals(
            array('min' => 1, 'max' => 0),
            HTML_QuickForm2_Rule_Length::mergeConfig(array('min' => 1), array('max' => 0))
        );

        $this->assertEquals(
            array('min' => 1, 'max' => 0),
            HTML_QuickForm2_Rule_Length::mergeConfig(
                array('min' => 1, 'max' => 5),
                array('max' => 0)
            )
        );
    }

    public function testEmptyFieldsAreSkipped()
    {
        $mockEmpty = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->setMethods(array('getType','getRawValue', 'setValue', '__toString'))
        ->getMock();
        $mockEmpty->expects($this->once())->method('getRawValue')
                  ->will($this->returnValue(''));
        $length = new HTML_QuickForm2_Rule_Length($mockEmpty, 'an error', array('min' => 5));
        $this->assertTrue($length->validate());
    }
}
