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

    protected function setUp() : void
    {
        $this->phpError     = null;
        $this->errorHandler = false;
    }

    protected function tearDown() : void
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

   /**
    * Class registered without include file must be loaded through autoloader,
    * not by guessing a file name from the class name
    */
    public function testCreateElementAutoloadedClass()
    {
        $className = 'QuickForm2Fixture\\AutoloadedElement';
        $autoload  = function ($class) use ($className) {
            if ($className === $class) {
                require dirname(__FILE__) . '/_files/AutoloadedElement.php';
            }
        };
        HTML_QuickForm2_Factory::registerElement('autoloaded', $className);
        spl_autoload_register($autoload);
        try {
            $el = HTML_QuickForm2_Factory::createElement('autoloaded', 'auto');
        } finally {
            spl_autoload_unregister($autoload);
        }
        $this->assertInstanceOf($className, $el);
        $this->assertEquals('auto', $el->name);
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
        ->onlyMethods($this->nodeAbstractMethods)
        ->getMock();
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $rule = HTML_QuickForm2_Factory::createRule('foo2', $mockNode);
    }

    public function testCreateRuleNonExistingClass()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->onlyMethods($this->nodeAbstractMethods)
        ->getMock();
        HTML_QuickForm2_Factory::registerRule('foo3', 'NonexistentClass');
        $this->expectException('HTML_QuickForm2_Exception_NotFound');
        $this->setErrorHandler();
        $rule = HTML_QuickForm2_Factory::createRule('foo3', $mockNode);
    }

    public function testCreateRuleNonExistingFile()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->onlyMethods($this->nodeAbstractMethods)
        ->getMock();
        HTML_QuickForm2_Factory::registerRule('foo4', 'NonexistentClass', 'NonexistentFile.php');
        $this->expectException('HTML_QuickForm2_Exception_NotFound');
        $this->setErrorHandler();
        $rule = HTML_QuickForm2_Factory::createRule('foo4', $mockNode);
    }

    public function testCreateRuleInvalidFile()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->onlyMethods($this->nodeAbstractMethods)
        ->getMock();
        HTML_QuickForm2_Factory::registerRule('foo5', 'NonexistentClass', dirname(__FILE__) . '/_files/InvalidFile.php');
        $this->expectException('HTML_QuickForm2_Exception_NotFound');
        $rule = HTML_QuickForm2_Factory::createRule('foo5', $mockNode);
    }

    public function testCreateRuleValid()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
        ->onlyMethods($this->nodeAbstractMethods)
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
