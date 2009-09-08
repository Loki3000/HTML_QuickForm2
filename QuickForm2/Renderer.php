<?php
/**
 * Base class for HTML_QuickForm2 renderers
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2009, Alexey Borzov <avb@php.net>,
 *                          Bertrand Mansion <golgote@mamasam.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Abstract base class for QuickForm2 renderers
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
abstract class HTML_Quickform2_Renderer
{
   /**
    * List of registered renderer types
    * @var array
    */
    private static $_types = array(
        'default' => array('HTML_QuickForm2_Renderer_Default',
                           'HTML/QuickForm2/Renderer/Default.php')
    );

   /**
    * List of registered renderer plugins
    * @var array
    */
    private static $_pluginClasses = array(
        'default' => array()
    );

   /**
    * Renderer instances
    * @var array
    */
    private static $_instances = array();

   /**
    * Plugins for this renderer instance
    * @var array
    */
    private $_plugins = array();

   /**
    * Plugin methods to call via __call() magic method
    *
    * Array has the form ('lowercase method name' => 'index in _plugins array')
    *
    * @var array
    */
    private $_pluginMethods = array();

   /**
    * Checks whether the file exists in the include path
    *
    * @param    string  file name
    * @return   bool
    */
    private static function _fileExists($fileName)
    {
        $fp = @fopen($fileName, 'r', true);
        if (is_resource($fp)) {
            fclose($fp);
            return true;
        }
        return false;
    }

   /**
    * Tries to load a given class from a given file
    *
    * @param    string  Class name to load
    * @param    string  Name of the file (supposedly) containing the given class
    * @throws   HTML_QuickForm2_NotFoundException   If the file either can't be
    *               loaded or doesn't contain the given class
    */
    private static function _loadClass($className, $includeFile)
    {
        if (empty($includeFile)) {
            throw new HTML_QuickForm2_NotFoundException(
                "Class '$className' does not exist and no file to load"
            );
        } elseif (!self::_fileExists($includeFile)) {
            throw new HTML_QuickForm2_NotFoundException("File '$includeFile' was not found");
        }
        // Do not silence the errors with @, parse errors will not be seen
        include $includeFile;
        // Still no class?
        if (!class_exists($className, false)) {
            throw new HTML_QuickForm2_NotFoundException(
                "Class '$className' was not found within file '$includeFile'"
            );
        }
    }

   /**
    * Returns the renderer instance of the given type
    *
    * Renderers are singletons, there can be only one renderer instance of
    * each registered type. This instance will auto-magically contain all the
    * plugins registered for this renderer type.
    *
    * @param    string  Type name (treated case-insensitively)
    * @return   HTML_QuickForm2_Renderer    A renderer instance
    * @throws   HTML_QuickForm2_InvalidArgumentException If type name is unknown
    * @throws   HTML_QuickForm2_NotFoundException If class for the renderer can
    *           not be found and/or loaded from file
    */
    final public static function getInstance($type)
    {
        $type = strtolower($type);
        if (!isset(self::$_instances[$type])) {
            if (!isset(self::$_types[$type])) {
                throw new HTML_QuickForm2_InvalidArgumentException(
                    "Renderer type '$type' is not known"
                );
            }
            list ($className, $includeFile) = self::$_types[$type];
            if (!class_exists($className, false)) {
                self::_loadClass($className, $includeFile);
            }
            $renderer = new $className;
            foreach (self::$_pluginClasses[$type] as $plugin) {
                if (!class_exists($plugin[0], false)) {
                    self::_loadClass($plugin[0], $plugin[1]);
                }
                $renderer->addPlugin(new $plugin[0]);
            }
            self::$_instances[$type] = $renderer;
        }
        return self::$_instances[$type];
    }

   /**
    * Registers a new renderer type
    *
    * @param    string  Type name (treated case-insensitively)
    * @param    string  Class name
    * @param    string  File containing the class, leave empty if class already loaded
    * @throws   HTML_QuickForm2_InvalidArgumentException if type already registered
    */
    final public static function register($type, $className, $includeFile = null)
    {
        $type = strtolower($type);
        if (!empty(self::$_types[$type])) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Renderer type '$type' is already registered"
            );
        }
        self::$_types[$type] = array($className, $includeFile);
        if (empty(self::$_pluginClasses[$type])) {
            self::$_pluginClasses[$type] = array();
        }
    }

   /**
    * Registers a plugin for a renderer type
    *
    * @param    string  Renderer type name (treated case-insensitively)
    * @param    string  Plugin class name
    * @param    string  File containing the plugin class, leave empty if class already loaded
    * @throws   HTML_QuickForm2_InvalidArgumentException if plugin is already registered
    */
    final public static function registerPlugin($type, $className, $includeFile = null)
    {
        $type = strtolower($type);
        // We don't check self::$_types, since a plugin may be registered
        // before renderer itself if it goes with some custom element
        if (empty(self::$_pluginClasses[$type])) {
            self::$_pluginClasses[$type] = array(array($className, $includeFile));
        } else {
            foreach (self::$_pluginClasses[$type] as $plugin) {
                if (0 == strcasecmp($plugin[0], $className)) {
                    throw new HTML_QuickForm2_InvalidArgumentException(
                        "Plugin '$className' for renderer type '$type' is already registered"
                    );
                }
            }
            self::$_pluginClasses[$type][] = array($className, $includeFile);
        }

        // If there is already a renderer instance, add the plugin instance to it
        if (isset(self::$_instances[$type])) {
            if (!class_exists($className, false)) {
                self::_loadClass($className, $includeFile);
            }
            self::$_instances[$type]->addPlugin(new $className);
        }
    }

   /**
    * Constructor
    *
    * Renderers are singletons, instances should be created by 
    * HTML_QuickForm_Renderer::getInstance()
    */
    protected function __construct()
    {
    }

   /**
    * Disallow cloning to enforce singleton
    */
    private function __clone()
    {
    }

   /**
    * Adds a plugin to the current renderer instance
    *
    * Plugin's methods are imported and can be later called as this object's own
    *
    * @param    HTML_QuickForm2_Renderer_Plugin     a plugin instance
    * @throws   HTML_QuickForm2_InvalidArgumentException if a plugin has already
    *                   imported name
    */
    final protected function addPlugin(HTML_QuickForm2_Renderer_Plugin $plugin)
    {
        $pluginsKey = count($this->_plugins);
        $methods    = array();
        foreach (array_map('strtolower', get_class_methods($plugin)) as $method) {
            if (method_exists($this, $method) ||
                method_exists('HTML_QuickForm2_Renderer_Plugin', $method)
            ) {
                continue;
            } elseif (isset($this->_pluginMethods[$method])) {
                throw new HTML_QuickForm2_InvalidArgumentException(
                    'Duplicate method name: ' . $method
                );
            }
            $methods[$method] = $pluginsKey;
        }
        $plugin->setRenderer($this);
        $this->_plugins[$pluginsKey]  = $plugin;
        $this->_pluginMethods        += $methods;
    }

   /**
    * Magic function; call an imported method of a plugin
    *
    * @param    string  method name
    * @param    array   method arguments
    * @return   mixed
    */
    final public function __call($name, $arguments)
    {
        $lower = strtolower($name);
        if (isset($this->_pluginMethods[$lower])) {
            return call_user_func_array(
                array($this->_plugins[$this->_pluginMethods[$lower]], $name),
                $arguments
            );
        }
        trigger_error("Fatal error: Call to undefined method " .
                      get_class($this) . "::" . $name . "()", E_USER_ERROR);
    }

   /**
    * Renders a generic element
    *
    * @param    HTML_QuickForm2_Node    Element being rendered
    */
    abstract public function renderElement(HTML_QuickForm2_Node $element);

   /**
    * Renders a hidden element
    *
    * @param    HTML_QuickForm2_Node    Hidden element being rendered
    */
    abstract public function renderHidden(HTML_QuickForm2_Node $element);

   /**
    * Renders a HTML_QuickForm2 object
    *
    * @param    HTML_QuickForm2_Node    Form being rendered
    */
    abstract public function renderForm(HTML_QuickForm2_Node $form);

   /**
    * Renders a container
    *
    * @param    HTML_QuickForm2_Node    Container being rendered
    */
    abstract public function renderContainer(HTML_QuickForm2_Node $container);
}
?>
