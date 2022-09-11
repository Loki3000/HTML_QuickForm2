<?php
/**
 * Class for <input type="hidden" /> elements
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
 * Class for <input type="hidden" /> elements
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_InputHidden extends HTML_QuickForm2_Element_Input
{
    protected $attributes = array('type' => 'hidden');

   /**
    * Hidden elements can not be frozen
    *
    * @param bool $freeze Whether element should be frozen or editable. This
    *                     parameter is ignored in case of hidden elements
    *
    * @return   bool    Always returns false
    */
    public function toggleFrozen($freeze = null)
    {
        return false;
    }

    /**
     * Disallows setting an error message on hidden elements
     *
     * @param string|null $error
     *
     * @return HTML_QuickForm2_Element_InputHidden
     * @throws HTML_QuickForm2_Exception_InvalidArgument if $error is not empty
     */
    public function setError($error = null)
    {
        if (strlen($error)) {
            throw new HTML_QuickForm2_Exception_InvalidArgument(
                "Hidden elements cannot have validation errors"
            );
        }
        return parent::setError($error);
    }

    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        $renderer->renderHidden($this);
        $this->renderClientRules($renderer->getJavascriptBuilder());
        return $renderer;
    }
}
?>
