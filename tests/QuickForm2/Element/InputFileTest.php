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
 * Unit test for HTML_QuickForm2_Element_InputFile class
 */
class HTML_QuickForm2_Element_InputFileTest extends PHPUnit\Framework\TestCase
{
    public function setUp() : void
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
        $this->assertStringContainsString('987654', $toobig->getError());

        $toobig2 = $form->appendChild(new HTML_QuickForm2_Element_InputFile('multi[]'));
        $this->assertFalse($form->validate());
        $this->assertStringContainsString('987654', $toobig2->getError());

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
            'messageProvider' => function ($messageId, $langId) {
                return "A nasty error happened!";
            }
        ));
        $this->assertFalse($form->validate());
        $this->assertEquals('A nasty error happened!', $upload->getError());
    }

    public function testObjectMessageProvider()
    {
        $mockProvider = $this->getMockBuilder('HTML_QuickForm2_MessageProvider')
        ->onlyMethods(array('get'))
        ->getMock();
        
        $mockProvider->expects($this->once())->method('get')
                     ->willReturn('A nasty error happened!');

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
