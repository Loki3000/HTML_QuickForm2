<?php
/**
 * Data source for HTML_QuickForm2 objects based on superglobal arrays
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
 * Data source for HTML_QuickForm2 objects based on superglobal arrays
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_DataSource_SuperGlobal extends HTML_QuickForm2_DataSource_Array implements HTML_QuickForm2_DataSource_Submit
{
    /**
     * Information on file uploads (from $_FILES)
     * @var array
     */
    protected $files = array();

    /**
     * Keys present in the $_FILES array
     * @var array
     */
    private static $_fileKeys = array('name', 'type', 'size', 'tmp_name', 'error');

    /**
     * magic quotes status
     * @var bool
     */
    protected $magicQuotesGPC = false;

    /**
     * Class constructor, intializes the internal arrays from superglobals
     *
     * @param string $requestMethod  Request method (GET or POST)
     * @param bool   $magicQuotesGPC Whether magic_quotes_gpc directive is on
     */
    public function __construct($requestMethod = 'POST', $magicQuotesGPC = false)
    {
        $this->magicQuotesGPC = $magicQuotesGPC;

        if (!$this->magicQuotesGPC) {
            if ('GET' == strtoupper($requestMethod)) {
                $this->values = $_GET;
            } else {
                $this->values = $_POST;
                //change $_FILES structure
                $this->files  = $this->parseFileArray($_FILES);
            }
        } else {
            if ('GET' == strtoupper($requestMethod)) {
                $this->values = $this->arrayMapRecursive('stripslashes', $_GET);
            } else {
                $this->values = $this->arrayMapRecursive('stripslashes', $_POST);
                $this->files  = $this->parseFileArray($_FILES);
            }
        }
    }

    /**
     * A recursive version of array_map() function
     *
     * @param callable $callback Callback function to apply
     * @param mixed    $arr      Input array
     *
     * @return    array with callback applied
     */
    protected function arrayMapRecursive($callback, $arr)
    {
        if (!is_array($arr)) {
            return call_user_func($callback, $arr);
        }
        $mapped = array();
        foreach ($arr as $k => $v) {
            $mapped[$k] = is_array($v)?
                          $this->arrayMapRecursive($callback, $v):
                          call_user_func($callback, $v);
        }
        return $mapped;
    }

    public function getUpload($name)
    {
        if (empty($this->files)) {
            return null;
        }
        
        //remove last empty token
        if ('[]'==substr($name, -2, 2)) {
            $name=substr($name, 0, -2);
        }

        if (false !== (strpos($name, '['))) {
            $tokens=explode('[', str_replace(']', '', $name));
            $value = $this->files;
            do {
                $token = array_shift($tokens);
                if (''==$token) {
                    $token=0;
                }
                if (!is_array($value)) {
                    return null;
                }
                if (!isset($value[$token])) {
                    return null;
                }
                $value = $value[$token];
            } while (!empty($tokens));

            if (isset($value['name']) || isset($value[0]['name'])) {
                return $value;
            }

            return null;
        } elseif (isset($this->files[$name]['name']) || isset($this->files[$name][0]['name'])) {
            return $this->files[$name];
        } else {
            return null;
        }
    }

    protected function parseFileArray($files)
    {
        foreach ($files as $key => $file) {
            $this->files[$key]=$this->recurseFileArray($file);
        }
        return $this->files;
    }

    protected function recurseFileArray($array, $result = array(), $root_node = null)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!$root_node) {
                    $result=$this->recurseFileArray($value, $result, $key);
                } else {
                    if (!array_key_exists($key, $result)) {
                        $result[$key]=[];
                    }
                    $result[$key]=$this->recurseFileArray($value, $result[$key], $root_node);
                }
            } else {
                if (!$root_node) {
                    $result[$key]=$value;
                } else {
                    if ('name' == $root_node && $this->magicQuotesGPC) {
                        //unescape filename for magic_quotes enabled
                        $result[$key][$root_node]=stripslashes($value);
                    } else {
                        $result[$key][$root_node]=$value;
                    }
                }
            }
        }
        return $result;
    }
}
