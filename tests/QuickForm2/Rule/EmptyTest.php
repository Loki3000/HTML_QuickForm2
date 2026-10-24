<?php
/**
 * Unit tests for HTML_QuickForm2 package
 *
 * PHP version 5
 *
 * LICENSE
 *
 * This source file is subject to BSD 3-Clause License that is bundled
 * with this package in the file LICENSE and available at the URL
 * https://raw.githubusercontent.com/pear/HTML_QuickForm2/trunk/docs/LICENSE
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
 * Unit test for HTML_QuickForm2_Rule_Empty class
 */
class HTML_QuickForm2_Rule_EmptyTest extends PHPUnit\Framework\TestCase
{
    public function testValidateGenericElement()
    {
        $mockValid = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->onlyMethods(array('getType','getRawValue', 'setValue', '__toString'))
        ->getMock();
        $mockValid->expects($this->once())->method('getRawValue')
                  ->willReturn('');
        $rule = new HTML_QuickForm2_Rule_Empty($mockValid, 'an error');
        $this->assertTrue($rule->validate());
        $this->assertEquals('', $mockValid->getError());

        $mockInvalid = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->onlyMethods(array('getType','getRawValue', 'setValue', '__toString'))
        ->getMock();
        $mockInvalid->expects($this->once())->method('getRawValue')
                    ->willReturn('some value');
        $rule2 = new HTML_QuickForm2_Rule_Empty($mockInvalid, 'an error');
        $this->assertFalse($rule2->validate());
        $this->assertEquals('an error', $mockInvalid->getError());
    }

    public function testValidateInputFileElement()
    {
        $mockValid = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
        ->onlyMethods(array('getValueArray'))
        ->getMock();
        $mockValid->expects($this->once())->method('getValueArray')
                  ->willReturn(array());
        $rule = new HTML_QuickForm2_Rule_Empty($mockValid, 'an error');
        $this->assertTrue($rule->validate());
        $this->assertEquals('', $mockValid->getError());

        $mockInvalid = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
        ->onlyMethods(array('getValueArray'))
        ->getMock();
        $mockInvalid->expects($this->once())->method('getValueArray')
                    ->willReturn(array(array(
                        'name'     => 'goodfile.php',
                        'type'     => 'application/octet-stream',
                        'tmp_name' => '/tmp/foobar',
                        'error'    => UPLOAD_ERR_OK,
                        'size'     => 1234
                    )));
        $rule2 = new HTML_QuickForm2_Rule_Empty($mockInvalid, 'an error');
        $this->assertFalse($rule2->validate());
        $this->assertEquals('an error', $mockInvalid->getError());
    }

    public function testFailedUploadIsNotEmpty()
    {
        $mockFailed = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
        ->onlyMethods(array('getValueArray'))
        ->getMock();
        $mockFailed->expects($this->once())->method('getValueArray')
                   ->willReturn(array(
                       'name'     => 'badfile.php',
                       'type'     => '',
                       'tmp_name' => '',
                       'error'    => UPLOAD_ERR_FORM_SIZE,
                       'size'     => 0
                   ));
        $rule = new HTML_QuickForm2_Rule_Empty($mockFailed, 'an error');
        $this->assertFalse($rule->validate());
        $this->assertEquals('an error', $mockFailed->getError());
    }

    public function testValidateArray()
    {
        $mockElEmpty = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->onlyMethods(array('getType','getRawValue', 'setValue', '__toString'))
        ->getMock();
        $mockElEmpty->expects($this->once())->method('getRawValue')
                    ->willReturn(array());
        $rule = new HTML_QuickForm2_Rule_Empty($mockElEmpty, 'an error');
        $this->assertTrue($rule->validate());

        $mockElNonEmpty = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->onlyMethods(array('getType','getRawValue', 'setValue', '__toString'))
        ->getMock();
        $mockElNonEmpty->expects($this->once())->method('getRawValue')
                       ->willReturn(array('foo', 'bar'));
        $rule = new HTML_QuickForm2_Rule_Empty($mockElNonEmpty, 'an error');
        $this->assertFalse($rule->validate());
    }
}
?>
