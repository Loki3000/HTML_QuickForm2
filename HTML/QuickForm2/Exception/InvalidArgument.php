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
 * Exception that denotes invalid arguments were passed
 *
 * One example is trying to create an element of type which is unknown to Factory
 * <code>
 * try {
 *     $el = HTML_QuickForm2_Factory::createElement('unknown');
 * } catch (HTML_QuickForm2_Exception_InvalidArgument $e) {
 *     echo $e->getMessage();
 * }
 * </code>
 * This code will output "Element type 'unknown' is not known"
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
s * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Exception_InvalidArgument extends HTML_QuickForm2_Exception
{
}
