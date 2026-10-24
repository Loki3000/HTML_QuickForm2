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
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/TestHelper.php';

/**
 * Unit test for HTML_QuickForm2_Controller_Action_Direct class
 */
class HTML_QuickForm2_Controller_Action_DirectTest extends PHPUnit\Framework\TestCase
{
    public function testPerform()
    {
        $source = $this->getMockBuilder('HTML_QuickForm2')
        ->onlyMethods(array('validate', 'getValue'))
        ->setConstructorArgs(array('source'))
        ->getMock();
        $source->expects($this->once())->method('validate')
               ->willReturn(true);
        $source->expects($this->once())->method('getValue')
               ->willReturn(array('foo' => 'bar'));
        $sourcePage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
        ->onlyMethods(array('populateForm'))
        ->setConstructorArgs(array($source))
        ->getMock();
        $sourcePage->addHandler('destination', new HTML_QuickForm2_Controller_Action_Direct());
        $destPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
        ->onlyMethods(array('populateForm'))
        ->setConstructorArgs(array(new HTML_QuickForm2('destination')))
        ->getMock();
        $mockJump = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
        ->onlyMethods(array('perform'))
        ->getMock();
        $mockJump->expects($this->once())->method('perform')
                 ->willReturn('jump to destination');
        $destPage->addHandler('jump', $mockJump);

        $controller = new HTML_QuickForm2_Controller('testDirectAction');
        $controller->addPage($sourcePage);
        $controller->addPage($destPage);

        $this->assertEquals('jump to destination', $sourcePage->handle('destination'));
        $this->assertTrue($controller->getSessionContainer()->getValidationStatus('source'));
        $this->assertEquals(array('foo' => 'bar'), $controller->getSessionContainer()->getValues('source'));
    }
}
