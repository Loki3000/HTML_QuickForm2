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
 * Unit test for HTML_QuickForm2_Controller_Page class
 */
class HTML_QuickForm2_Controller_PageTest extends PHPUnit\Framework\TestCase
{
    public function testPopulateFormOnce()
    {
        $mockPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
        ->onlyMethods(array('populateForm'))
        ->setConstructorArgs(array(new HTML_QuickForm2('firstPage')))
        ->getMock();
            
        $mockPage->expects($this->once())->method('populateForm');

        $mockPage->populateFormOnce();
        $mockPage->populateFormOnce();
    }

    public function testActionHandlerPrecedence()
    {
        $controller = new HTML_QuickForm2_Controller('precedence');
        $mockPage   = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
        ->onlyMethods(array('populateForm'))
        ->setConstructorArgs(array(new HTML_QuickForm2('precedencePage')))
        ->getMock();
            
        $controller->addPage($mockPage);
        $this->expectException('HTML_QuickForm2_Exception_NotFound');
        $mockPage->handle('foo');

        $mockFoo1 = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
        ->onlyMethods(array('perform'))
        ->getMock();
            
        $mockFoo1->expects($this->once())->method('perform')
                 ->willReturn('foo common');
        $controller->addHandler('foo', $mockFoo1);
        $this->assertEquals('foo common', $mockPage->handle('foo'));

        $mockFoo2 = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
        ->onlyMethods(array('perform'))
        ->getMock();
            
        $mockFoo2->expects($this->once())->method('perform')
                 ->willReturn('foo specific');
        $mockPage->addHandler('foo', $mockFoo2);
        $this->assertEquals('foo specific', $mockPage->handle('foo'));
    }

    public function testDefaultActionHandler()
    {
        $controller = new HTML_QuickForm2_Controller('defaultDisplay');
        $mockPage   = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
        ->onlyMethods(array('populateForm'))
        ->setConstructorArgs(array(new HTML_QuickForm2('defaultDisplayPage')))
        ->getMock();
            
        $controller->addPage($mockPage);

        ob_start();
        $mockPage->handle('display');
        $this->assertStringContainsString($mockPage->getForm()->__toString(), ob_get_contents());
        ob_end_clean();
    }

    public function testSetDefaultAction()
    {
        $mockPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
        ->onlyMethods(array('populateForm'))
        ->setConstructorArgs(array(new HTML_QuickForm2('defaultActionPage')))
        ->getMock();
            
        $mockPage->setDefaultAction('foo', 'empty.gif');

        $default = $mockPage->getForm()->getElementById('qf:default-action');
        $this->assertNotNull($default);
        $this->assertEquals($mockPage->getButtonName('foo'), $default->getName());
        $this->assertEquals('empty.gif', $default->getAttribute('src'));

        $mockPage->setDefaultAction('bar');
        $this->assertEquals($mockPage->getButtonName('bar'), $default->getName());
        $this->assertStringContainsString('base64', $default->getAttribute('src'));
    }

    public function testDefaultActionIsFirstElement()
    {
        $mockPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
        ->onlyMethods(array('populateForm'))
        ->setConstructorArgs(array(new HTML_QuickForm2('defaultActionPage')))
        ->getMock();
            
        $mockPage->getForm()->addElement('text', 'first');
        $mockPage->getForm()->addElement('text', 'second');
        $mockPage->setDefaultAction('foo', 'empty.gif');

        foreach ($mockPage->getForm() as $el) {
            $this->assertEquals('qf:default-action', $el->getId());
            break;
        }
    }

    public function testPropagateControllerId()
    {
        $noPropPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
        ->onlyMethods(array('populateForm'))
        ->setConstructorArgs(array(new HTML_QuickForm2('noPropagateForm')))
        ->getMock();
            
        $noPropController = new HTML_QuickForm2_Controller('foo', true, false);
        $noPropController->addPage($noPropPage);
        $noPropPage->populateFormOnce();
        $hidden = $noPropPage->getForm()->getElementsByName(HTML_QuickForm2_Controller::KEY_ID);
        $this->assertEquals(0, count($hidden));

        $propPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
        ->onlyMethods(array('populateForm'))
        ->setConstructorArgs(array(new HTML_QuickForm2('propagateForm')))
        ->getMock();
            
        $propController = new HTML_QuickForm2_Controller('bar', true, true);
        $propController->addPage($propPage);
        $propPage->populateFormOnce();
        $hidden = $propPage->getForm()->getElementsByName(HTML_QuickForm2_Controller::KEY_ID);
        $this->assertNotEquals(0, count($hidden));
        $this->assertEquals('bar', $hidden[0]->getValue());
    }
}
?>