<?php
/**
 * Rule checking that the field is empty
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
 * Rule checking that the field is empty
 *
 * Handles both simple form fields and file uploads, the latter are considered
 * valid iff no file upload was attempted.
 *
 * The rule doesn't make much sense if used separately, but can be very helpful
 * if chained:
 * <code>
 * $spamCheck->addRule('empty')
 *           ->or_($email->createRule('nonempty', 'Supply a valid email if you want to receive our spam')
 *                       ->and_($email->createRule('email')));
 * </code>
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Rule_Empty extends HTML_QuickForm2_Rule
{
    protected function validateOwner()
    {
        $value = $this->owner->getValue();
        if ($this->owner instanceof HTML_QuickForm2_Element_InputFile) {
            return empty($this->owner->getValueArray());
        } elseif (is_array($value)) {
            return 0 == count(array_filter($value, function ($v) {
                return '' !== (string)$v;
            }));
        } else {
            return 0 == strlen((string)$value);
        }
    }

    protected function getJavascriptCallback()
    {
        return "function() { return qf.rules.empty(" . $this->owner->getJavascriptValue() . "); }";
    }
}

?>