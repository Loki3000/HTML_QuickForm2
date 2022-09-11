<?php
/**
 * Base class for fieldsets
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
 * Concrete implementation of a container for fieldsets
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Container_Fieldset extends HTML_QuickForm2_Container
{
   /**
    * Fieldsets don't have a 'name' attribute, so we only handle 'id'
    * @var array
    */
    protected $watchedAttributes = array('id');

    public function getType()
    {
        return 'fieldset';
    }


    public function getName()
    {
        return null;
    }


    public function setName($name)
    {
        // Fieldsets do not have a name attribute
        return $this;
    }


    public function setValue($value)
    {
        throw new HTML_QuickForm2_Exception('Not implemented');
    }
}
?>