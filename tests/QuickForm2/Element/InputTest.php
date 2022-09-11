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
 * We need to set the element's type
 */
class HTML_QuickForm2_Element_InputImpl extends HTML_QuickForm2_Element_Input
{
    public function __construct($name = null, $attributes = null, array $data = array())
    {
        parent::__construct($name, $attributes, $data);
        $this->attributes['type'] = 'concrete';
    }
}


/**
 * Unit test for HTML_QuickForm2_Element_Input class
 */
class HTML_QuickForm2_Element_InputTest extends PHPUnit\Framework\TestCase
{
    public function testTypeAttributeIsReadonly()
    {
        $obj = new HTML_QuickForm2_Element_InputImpl();
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $obj->removeAttribute('type');
        $obj = new HTML_QuickForm2_Element_InputImpl();
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $obj->setAttribute('type', 'bogus');
    }

    public function testCanSetAndGetValue()
    {
        $obj = new HTML_QuickForm2_Element_InputImpl();

        $this->assertSame($obj, $obj->setValue('foo'));
        $this->assertEquals($obj->getValue(), 'foo');

        $obj->setAttribute('value', 'bar');
        $this->assertEquals($obj->getValue(), 'bar');

        $obj->setAttribute('disabled');
        $this->assertNull($obj->getValue());
    }

    public function testSetNullValue()
    {
        $obj = new HTML_QuickForm2_Element_InputImpl();
        $obj->setValue(null);

        $this->assertEquals('', $obj->getValue());
    }

    public function testHtmlGeneration()
    {
        $obj = new HTML_QuickForm2_Element_InputImpl();
        $this->assertMatchesRegularExpression('!<input[^>]*type="concrete"[^>]*/>!', $obj->__toString());
    }

    public function testFrozenHtmlGeneration()
    {
        $obj = new HTML_QuickForm2_Element_InputImpl('test');
        $obj->setValue('bar');
        $obj->toggleFrozen(true);

        $obj->persistentFreeze(false);
        $this->assertDoesNotMatchRegularExpression('/[<>]/', $obj->__toString());
        $this->assertMatchesRegularExpression('/bar/', $obj->__toString());

        $obj->persistentFreeze(true);
        $this->assertMatchesRegularExpression('!<input[^>]*type="hidden"[^>]*/>!', $obj->__toString());

        $obj->setAttribute('disabled');
        $this->assertMatchesRegularExpression('/bar/', $obj->__toString());
        $this->assertDoesNotMatchRegularExpression('!<input[^>]*type="hidden"[^>]*/>!', $obj->__toString());
    }
}
?>