<?php
/**
 * Exception classes for HTML_QuickForm2
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
 * Exception that denotes some resource was not found
 *
 * One example is trying to instantiate a nonexistent class in Factory
 * <code>
 * try {
 *     HTML_QuickForm2_Factory::registerElement('missing', 'NonExistent');
 *     $el = HTML_QuickForm2_Factory::createElement('missing');
 * } catch (HTML_QuickForm2_Exception_NotFound $e) {
 *     echo $e->getMessage();
 * }
 * </code>
 * This code fill output "Class 'NonExistent' does not exist and no file to load"
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Exception_NotFound extends HTML_QuickForm2_Exception
{
}
