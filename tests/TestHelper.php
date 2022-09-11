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

// If running from SVN checkout, update include_path
if ('@' . 'package_version@' == '@package_version@') {
    $classPath   = realpath(dirname(dirname(__FILE__)));
    $includePath = array_map('realpath', explode(PATH_SEPARATOR, get_include_path()));
    if (0 !== ($key = array_search($classPath, $includePath))) {
        if (false !== $key) {
            unset($includePath[$key]);
        }
        set_include_path($classPath . PATH_SEPARATOR . implode(PATH_SEPARATOR, $includePath));
    }
}

require_once 'vendor/autoload.php';
require_once 'HTML/QuickForm2/Loader.php';
spl_autoload_register(array('HTML_QuickForm2_Loader', 'autoload'));

if (strpos($_SERVER['argv'][0], 'phpunit') === false) {
    /** Include PHPUnit dependencies based on version */
    require_once 'PHPUnit/Runner/Version.php';
    if (version_compare(PHPUnit\Runner\Version::id(), '3.5.0', '>=')) {
        require_once 'PHPUnit/Autoload.php';
    } else {
        require_once 'PHPUnit/Framework.php';
    }
}
?>