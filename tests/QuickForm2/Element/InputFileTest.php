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
 * Unit test for HTML_QuickForm2_Element_InputFile class
 */
class HTML_QuickForm2_Element_InputFileTest extends PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $_FILES = array(
            'foo' => array(
                'name'      => 'file.doc',
                'tmp_name'  => '/tmp/nothing',
                'type'      => 'text/plain',
                'size'      => 1234,
                'error'     => UPLOAD_ERR_OK
            ),
            'toobig' => array(
                'name'      => 'ahugefile.zip',
                'tmp_name'  => '',
                'type'      => '',
                'size'      => 0,
                'error'     => UPLOAD_ERR_FORM_SIZE
            ),
            'local' => array(
                'name'      => 'nasty-trojan.exe',
                'tmp_name'  => '',
                'type'      => '',
                'size'      => 0,
                'error'     => UPLOAD_ERR_CANT_WRITE
            ),
            'multi' => array(
                'name' => array(
                    0       => 'file.doc'
                ),
                'tmp_name' => array(
                    0       => '/tmp/nothing'
                ),
                'type' => array(
                    0       => 'text/plain'
                ),
                'size' => array(
                    0       => 1234
                ),
                'error' => array(
                    0       => UPLOAD_ERR_FORM_SIZE
                ),
            )
        );
        $_POST = array(
            'MAX_FILE_SIZE' => '987654'
        );
    }

    public function testCannotBeFrozen()
    {
        $upload = new HTML_QuickForm2_Element_InputFile('foo');
        $this->assertFalse($upload->toggleFrozen(true));
        $this->assertFalse($upload->toggleFrozen());
    }

    public function testSetValueFromSubmitDataSource()
    {
        $form = new HTML_QuickForm2('upload', 'post', null, false);
        $foo = $form->appendChild(new HTML_QuickForm2_Element_InputFile('foo'));
        $bar = $form->appendChild(new HTML_QuickForm2_Element_InputFile('bar'));
        $multi = $form->appendChild(new HTML_QuickForm2_Element_InputFile('multi[]'));

        $this->assertNull($bar->getValue());
        $this->assertEquals(array(
            'name'      => 'file.doc',
            'tmp_name'  => '/tmp/nothing',
            'type'      => 'text/plain',
            'size'      => 1234,
            'error'     => UPLOAD_ERR_OK
        ), $foo->getValue());

        $this->assertEquals(array(array(
            'name'      => 'file.doc',
            'tmp_name'  => '/tmp/nothing',
            'type'      => 'text/plain',
            'size'      => 1234,
            'error'     => UPLOAD_ERR_FORM_SIZE
        )), $multi->getValue());
    }

    public function testBuiltinValidation()
    {
        $form = new HTML_QuickForm2('upload', 'post', null, false);
        $foo  = $form->appendChild(new HTML_QuickForm2_Element_InputFile('foo'));
        $this->assertTrue($form->validate());

        $toobig = $form->appendChild(new HTML_QuickForm2_Element_InputFile('toobig'));
        $this->assertFalse($form->validate());
        $this->assertContains('987654', $toobig->getError());

        $toobig2 = $form->appendChild(new HTML_QuickForm2_Element_InputFile('multi[]'));
        $this->assertFalse($form->validate());
        $this->assertContains('987654', $toobig2->getError());

    }

    public function testInvalidMessageProvider()
    {
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $invalid = new HTML_QuickForm2_Element_InputFile('invalid', null, array('messageProvider' => array()));
    }

    public function testCallbackMessageProvider()
    {
        $form   = new HTML_QuickForm2('upload', 'post', null, false);
        $upload = $form->addFile('local', array(), array(
            'messageProvider' => create_function('$messageId, $langId', 'return "A nasty error happened!";')
        ));
        $this->assertFalse($form->validate());
        $this->assertEquals('A nasty error happened!', $upload->getError());
    }

    public function testObjectMessageProvider()
    {
        $mockProvider = $this->getMockBuilder('HTML_QuickForm2_MessageProvider')
        ->setMethods(array('get'))
        ->getMock();
        
        $mockProvider->expects($this->once())->method('get')
                     ->will($this->returnValue('A nasty error happened!'));

        $form   = new HTML_QuickForm2('upload', 'post', null, false);
        $upload = $form->addFile('local', array(), array(
            'messageProvider' => $mockProvider
        ));
        $this->assertFalse($form->validate());
        $this->assertEquals('A nasty error happened!', $upload->getError());
    }

   /**
    * File should check that the form has POST method, set enctype to multipart/form-data
    * @see http://pear.php.net/bugs/bug.php?id=16807
    */
    public function testRequest16807()
    {
        $form = new HTML_QuickForm2('broken', 'get');
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $form->addFile('upload', array('id' => 'upload'));
        $this->fail('Expected HTML_QuickForm2_Exception_InvalidArgument was not thrown');

        $group = HTML_QuickForm2_Factory::createElement('group', 'fileGroup');
        $group->addFile('upload', array('id' => 'upload'));
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $form->appendChild($group);
        $this->fail('Expected HTML_QuickForm2_Exception_InvalidArgument was not thrown');

        $post = new HTML_QuickForm2('okform', 'post');
        $this->assertNull($post->getAttribute('enctype'));
        $post->addFile('upload');
        $this->assertEquals('multipart/form-data', $post->getAttribute('enctype'));
    }
}
?>
