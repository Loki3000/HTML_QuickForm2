<?php
/**
 * A hidden button used to submit the form when the user presses Enter
 *
 * PHP version 5
 *
 * LICENSE
 *
 * This source file is subject to BSD 3-Clause License that is bundled
 * with this package in the file LICENSE and available at the URL
 * https://raw.githubusercontent.com/pear/HTML_QuickForm2/trunk/docs/LICENSE
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * A hidden button used to submit the form when the user presses Enter
 *
 * This element is used by {@link HTML_QuickForm2_Controller_Page::setDefaultAction()}
 * to define the action that will take place if the user presses Enter on one
 * of the form elements instead of explicitly clicking one of the submit
 * buttons. Injecting a hidden <input type="image" /> element is about the
 * only cross-browser way to achieve this.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 * @link     http://www.alanflavell.org.uk/www/formquestion.html
 * @link     http://muffinresearch.co.uk/archives/2005/12/08/fun-with-multiple-submit-buttons/
 */
class HTML_QuickForm2_Controller_DefaultAction
    extends HTML_QuickForm2_Element_InputImage
{
    protected $attributes = array('type' => 'image', 'id' => 'qf:default-action');

   /**
    * Disallow changing the 'id' attribute
    *
    * @param string $name  Attribute name
    * @param string $value Attribute value, null if attribute is being removed
    */
    protected function onAttributeChange($name, $value = null)
    {
        if ('id' == $name) {
            throw new HTML_QuickForm2_Exception_InvalidArgument(
                "Attribute 'id' is read-only"
            );
        }
        parent::onAttributeChange($name, $value);
    }

   /**
    * This element is rendered using renderHidden() method
    *
    * renderHidden() is used to
    *   - prevent using the standard element template as this button is
    *     expected to be hidden
    *   - render it above all other submit buttons since hidden elements
    *     are usually at the top of the form
    *
    * @param HTML_QuickForm2_Renderer $renderer
    *
    * @return   HTML_QuickForm2_Renderer
    */
    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        $renderer->renderHidden($this);
        return $renderer;
    }
}
?>
