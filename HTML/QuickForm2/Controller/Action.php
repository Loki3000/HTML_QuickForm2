<?php
/**
 * Interface for Controller action handlers
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
 * Interface for Controller action handlers
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
interface HTML_QuickForm2_Controller_Action
{
   /**
    * Performs the given action upon the given page
    *
    * @param HTML_QuickForm2_Controller_Page $page page of the multipage form
    * @param string                          $name action name, some action handlers
    *                                           may perform different tasks depending
    *                                           on this
    */
    public function perform(HTML_QuickForm2_Controller_Page $page, $name);
}
?>