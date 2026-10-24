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
 * Unit test for HTML_QuickForm2_Rule_MimeType class
 */
class HTML_QuickForm2_Rule_MimeTypeTest extends PHPUnit\Framework\TestCase
{
    public function testMimeTypeIsRequired()
    {
        $file = new HTML_QuickForm2_Element_InputFile('foo');
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $mimeType = new HTML_QuickForm2_Rule_MimeType($file, 'an error');
    }

    public function testCanOnlyValidateFileUploads()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
        ->onlyMethods(array('getType','getRawValue', 'setValue', '__toString'))
        ->getMock();
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $mimeType = new HTML_QuickForm2_Rule_MimeType($mockEl, 'an error', 'text/plain');
    }

    public function testMissingUploadsAreSkipped()
    {
        $mockNoUpload = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
        ->onlyMethods(array('getValueArray'))
        ->getMock();
        $mockNoUpload->expects($this->once())->method('getValueArray')
                     ->willReturn(array());
        $mimeType = new HTML_QuickForm2_Rule_MimeType($mockNoUpload, 'an error', 'text/plain');
        $this->assertTrue($mimeType->validate());
    }

    public function testOptionsHandling()
    {
        $mockFile = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
        ->onlyMethods(array('getValueArray'))
        ->getMock();
        $mockFile->expects($this->exactly(2))->method('getValueArray')
                 ->willReturn(array(array(
                    'name'     => 'pr0n.jpg',
                    'type'     => 'image/jpeg',
                    'tmp_name' => '/tmp/foobar',
                    'error'    => UPLOAD_ERR_OK,
                    'size'     => 123456
                 )));
        $typeText = new HTML_QuickForm2_Rule_MimeType($mockFile, 'need text', 'text/plain');
        $this->assertFalse($typeText->validate());

        $typeImage = new HTML_QuickForm2_Rule_MimeType(
            $mockFile,
            'need image',
            array('image/gif', 'image/jpeg')
        );
        $this->assertTrue($typeImage->validate());
    }

    public function testConfigHandling()
    {
        $mockFile = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
        ->onlyMethods(array('getValueArray'))
        ->getMock();
        $mockFile->expects($this->exactly(2))->method('getValueArray')
                 ->willReturn(array(array(
                    'name'     => 'pr0n.jpg',
                    'type'     => 'image/jpeg',
                    'tmp_name' => '/tmp/foobar',
                    'error'    => UPLOAD_ERR_OK,
                    'size'     => 123456
                 )));

        HTML_QuickForm2_Factory::registerRule(
            'type-text',
            'HTML_QuickForm2_Rule_MimeType',
                                              null,
            'text/plain'
        );
        $typeText = $mockFile->addRule('type-text', 'need text');
        $this->assertFalse($typeText->validate());

        HTML_QuickForm2_Factory::registerRule(
            'type-image',
            'HTML_QuickForm2_Rule_MimeType',
                                              null,
            array('image/gif', 'image/jpeg')
        );
        $typeImage = $mockFile->addRule('type-image', 'need image');
        $this->assertTrue($typeImage->validate());
    }

    public function testConfigOverridesOptions()
    {
        $mockFile = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
        ->onlyMethods(array('getValueArray'))
        ->getMock();
        $mockFile->expects($this->once())->method('getValueArray')
                 ->willReturn(array(array(
                    'name'     => 'pr0n.jpg',
                    'type'     => 'image/jpeg',
                    'tmp_name' => '/tmp/foobar',
                    'error'    => UPLOAD_ERR_OK,
                    'size'     => 123456
                 )));
        HTML_QuickForm2_Factory::registerRule(
            'type-override-text',
            'HTML_QuickForm2_Rule_MimeType',
                                              null,
            'text/plain'
        );
        $mimeType = $mockFile->addRule(
            'type-override-text',
            'need image',
                                       array('image/gif', 'image/jpeg')
        );
        $this->assertFalse($mimeType->validate());
    }
}
