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
 * Unit test for HTML_QuickForm2_JavascriptBuilder class
 */
class HTML_QuickForm2_JavascriptBuilderTest extends PHPUnit\Framework\TestCase
{
    public function testEncode()
    {
        $this->assertEquals('null', HTML_QuickForm2_JavascriptBuilder::encode(null));
        $this->assertEquals('false', HTML_QuickForm2_JavascriptBuilder::encode(false));
        $this->assertEquals('"foo"', HTML_QuickForm2_JavascriptBuilder::encode('foo'));
        $this->assertEquals('"\r\n\t\\\'\"bar\\\\"', HTML_QuickForm2_JavascriptBuilder::encode("\r\n\t'\"bar\\"));
        $this->assertEquals(1, HTML_QuickForm2_JavascriptBuilder::encode(1));

        $this->assertEquals('[]', HTML_QuickForm2_JavascriptBuilder::encode(array()));
        $this->assertEquals('{}', HTML_QuickForm2_JavascriptBuilder::encode(new stdClass()));

        $this->assertEquals('["a","b"]', HTML_QuickForm2_JavascriptBuilder::encode(array('a', 'b')));
        $this->assertEquals('{"0":"a","b":"c"}', HTML_QuickForm2_JavascriptBuilder::encode(array('a', 'b' => 'c')));

        $obj = new stdClass();
        $obj->a = 'b';
        $obj->c = 'd';
        $obj->e = array('f', 'g');
        $this->assertEquals('{"a":"b","c":"d","e":["f","g"]}', HTML_QuickForm2_JavascriptBuilder::encode($obj));

        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $fp = fopen(__FILE__, 'rb');
        HTML_QuickForm2_JavascriptBuilder::encode($fp);
        fclose($fp);
    }

    public function testBaseLibrary()
    {
        $builder = new HTML_QuickForm2_JavascriptBuilder();

        $libraries = $builder->getLibraries(false, false);
        $this->assertArrayHasKey('base', $libraries);
        $this->assertStringNotContainsString('<script', $libraries['base']);

        $libraries = $builder->getLibraries(false, true);
        $this->assertStringContainsString('<script', $libraries['base']);

        $libraries = $builder->getLibraries(true, false);
        $this->assertStringContainsString('qf.Validator', $libraries);
        $this->assertStringNotContainsString('<script', $libraries);

        $libraries = $builder->getLibraries(true, true);
        $this->assertStringContainsString('qf.Validator', $libraries);
        $this->assertStringContainsString('<script', $libraries);
    }

    public function testInlineMissingLibrary()
    {
        $builder = new HTML_QuickForm2_JavascriptBuilder();
        $builder->addLibrary('missing', 'missing.js');

        $this->expectException('HTML_QuickForm2_Exception_NotFound');
        $libraries = $builder->getLibraries(true);
    }

    public function testFormJavascript()
    {
        $builder = new HTML_QuickForm2_JavascriptBuilder();
        $element = new HTML_QuickForm2_Element_InputText();

        $mockRuleOne = $this->getMockBuilder('HTML_QuickForm2_Rule')
        ->onlyMethods(array('validateOwner', 'getJavascriptCallback'))
        ->setConstructorArgs(array($element))
        ->getMock();
        $mockRuleOne->expects($this->once())->method('getJavascriptCallback')
            ->willReturn('jsRuleOne');

        $mockRuleTwo = $this->getMockBuilder('HTML_QuickForm2_Rule')
        ->onlyMethods(array('validateOwner', 'getJavascriptCallback'))
        ->setConstructorArgs(array($element))
        ->getMock();
        $mockRuleTwo->expects($this->once())->method('getJavascriptCallback')
            ->willReturn('jsRuleTwo');

        $this->assertEquals('', $builder->getFormJavascript());

        $builder->setFormId('formOne');
        $builder->addRule($mockRuleOne);
        $builder->addElementJavascript('setupCodeOne');

        $builder->setFormId('formTwo');
        $builder->addRule($mockRuleTwo);
        $builder->addElementJavascript('setupCodeTwo');

        $scriptOne = $builder->getFormJavascript('formOne', false);
        $this->assertStringContainsString('jsRuleOne', $scriptOne);
        $this->assertStringContainsString('setupCodeOne', $scriptOne);
        $this->assertStringNotContainsString('jsRuleTwo', $scriptOne);
        $this->assertStringNotContainsString('setupCodeTwo', $scriptOne);
        $this->assertStringNotContainsString('<script', $scriptOne);

        $scriptTwo = $builder->getFormJavascript('formTwo', true);
        $this->assertStringNotContainsString('jsRuleOne', $scriptTwo);
        $this->assertStringNotContainsString('setupCodeOne', $scriptTwo);
        $this->assertStringContainsString('jsRuleTwo', $scriptTwo);
        $this->assertStringContainsString('setupCodeTwo', $scriptTwo);
        $this->assertStringContainsString('<script', $scriptTwo);

        $scriptBoth = $builder->getFormJavascript();
        $this->assertStringContainsString('jsRuleOne', $scriptBoth);
        $this->assertStringContainsString('setupCodeTwo', $scriptBoth);
    }
}
