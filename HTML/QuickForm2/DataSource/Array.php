<?php
/**
 * Array-based data source for HTML_QuickForm2 objects
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
 * Array-based data source for HTML_QuickForm2 objects
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_DataSource_Array implements HTML_QuickForm2_DataSource_NullAware
{
   /**
    * Array containing elements' values
    * @var array
    */
    protected $values;

   /**
    * Class constructor, initializes the values array
    *
    * @param array $values Array containing the elements' values
    */
    public function __construct(array $values = array())
    {
        $this->values = $values;
    }

    public function getValue($name)
    {
        if (empty($this->values)) {
            return null;
        }
        
        if (strpos((string)$name, '[')) {
            $tokens = explode('[', str_replace(']', '', $name));
            $value = $this->values;
            do {
                $token = array_shift($tokens);
                if (!is_array($value) || !isset($value[$token])) {
                    return null;
                }
                $value = $value[$token];
            } while (!empty($tokens));
            return $value;
        } elseif (isset($this->values[$name])) {
            return $this->values[$name];
        }

        return null;
    }

    public function hasValue($name)
    {
        if (empty($this->values)) {
            return false;

        } elseif (strpos((string)$name, '[')) {
            $tokens = explode('[', str_replace(']', '', $name));
            $value  = $this->values;
            do {
                $token = array_shift($tokens);
                if (!is_array($value) || !array_key_exists($token, $value)) {
                    return false;
                }
                $value = $value[$token];
            } while (!empty($tokens));
            return true;
        } else {
            return array_key_exists($name, $this->values);
        }
    }
}
?>
