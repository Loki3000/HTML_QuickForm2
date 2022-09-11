<?php
/**
 * Class with static methods for loading classes and files
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
 * Class with static methods for loading classes and files
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Loader
{
   /**
    * Tries to load a given class
    *
    * If no $includeFile was provided, $className will be used with underscores
    * replaced with path separators and '.php' extension appended
    *
    * @param string $className   Class name to load
    * @param string $includeFile Name of the file (supposedly) containing the given class
    *
    * @throws   HTML_QuickForm2_Exception_NotFound   If the file either can't be
    *               loaded or doesn't contain the given class
    */
    public static function loadClass($className, $includeFile = null)
    {
        if (class_exists($className, false) || interface_exists($className, false)) {
            return;
        }

        if (empty($includeFile)) {
            $includeFile = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        }
        // Do not silence the errors with @, parse errors will not be seen

        include $includeFile;

        // Still no class?
        if (!class_exists($className, false) && !interface_exists($className, false)) {
            if (!self::fileExists($includeFile)) {
                throw new HTML_QuickForm2_Exception_NotFound(
                    "File '$includeFile' was not found"
                );
            } else {
                throw new HTML_QuickForm2_Exception_NotFound(
                    "Class '$className' was not found within file '$includeFile'"
                );
            }
        }
    }

   /**
    * Checks whether the file exists in the include path
    *
    * @param string $fileName file name
    *
    * @return   bool
    */
    public static function fileExists($fileName)
    {
        $fp = @fopen($fileName, 'r', true);
        if (is_resource($fp)) {
            fclose($fp);
            return true;
        }
        return false;
    }

   /**
    * Loading of HTML_QuickForm2_* classes suitable for SPL autoload mechanism
    *
    * This method will only try to load a class if its name starts with
    * HTML_QuickForm2. Register with the following:
    * <code>
    * spl_autoload_register(array('HTML_QuickForm2_Loader', 'autoload'));
    * </code>
    *
    * @param string $class Class name
    *
    * @return   bool    Whether class loaded successfully
    */
    public static function autoload($class)
    {
        if (0 !== strpos($class, 'HTML_QuickForm2')) {
            return false;
        }
        try {
            self::loadClass($class);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
