<?php
/**
 * Class for static elements that only contain text or markup
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2014, Alexey Borzov <avb@php.net>,
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
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Class for static elements that only contain text or markup
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_Static extends HTML_QuickForm2_Element
{
   /**
    * Name of the tag to wrap around static element's content
    * @var string
    */
    protected $tagName = null;

   /**
    * Whether to output closing tag when $tagName is set and element's content is empty
    * @var bool
    */
    protected $forceClosingTag = true;

   /**
    * Contains options and data used for the element creation
    * - content: Content of the static element
    * @var  array
    */
    protected $data = array('content' => '');

   /**
    * Class constructor
    *
    * Static element can understand the following keys in $data parameter:
    *   - 'content': content of the static element, e.g. text or markup
    *   - 'tagName': name of the tag to wrap around content, e.g. 'div'.
    *     Using tag names corresponding to form elements will cause an Exception
    *   - 'forceClosingTag': whether to output closing tag in case of empty
    *     content, &lt;foo&gt;&lt;/foo&gt; vs. &lt;foo /&gt;
    *
    * @param string       $name       Element name
    * @param string|array $attributes Attributes (either a string or an array)
    * @param array        $data       Additional element data
    */
    public function __construct($name = null, $attributes = null, array $data = array())
    {
        if (!empty($data['tagName'])) {
            $this->setTagName(
                $data['tagName'],
                !array_key_exists('forceClosingTag', $data) || $data['forceClosingTag']
            );
        }
        unset($data['tagName'], $data['forceClosingTag']);
        parent::__construct($name, $attributes, $data);
    }

   /**
    * Intercepts setting 'name' and 'id' attributes
    *
    * Overrides parent method to allow removal of 'name' attribute on Static
    * elements
    *
    * @param string $name  Attribute name
    * @param string $value Attribute value, null if attribute is being removed
    *
    * @throws   HTML_QuickForm2_Exception_InvalidArgument    if trying to
    *                                   remove a required attribute
    */
    protected function onAttributeChange($name, $value = null)
    {
        if ('name' == $name && null === $value) {
            unset($this->attributes['name']);
        } else {
            parent::onAttributeChange($name, $value);
        }
    }

   /**
    * Sets the element's name
    *
    * Passing null here will remove the name attribute
    *
    * @param string|null $name
    *
    * @return   HTML_QuickForm2_Element_Static
    */
    public function setName($name)
    {
        if (null !== $name) {
            return parent::setName($name);
        } else {
            return $this->removeAttribute('name');
        }
    }

    public function getType()
    {
        return 'static';
    }

   /**
    * Static element can not be frozen
    *
    * @param bool $freeze Whether element should be frozen or editable. This
    *                     parameter is ignored in case of static elements
    *
    * @return   bool    Always returns false
    */
    public function toggleFrozen($freeze = null)
    {
        return false;
    }

   /**
    * Sets the contents of the static element
    *
    * @param string $content Static content
    *
    * @return $this
    */
    function setContent($content)
    {
        $this->data['content'] = $content;
        return $this;
    }

   /**
    * Returns the contents of the static element
    *
    * @return   string
    */
    function getContent()
    {
        return $this->data['content'];
    }

   /**
    * Static element's content can also be set via this method
    *
    * @param mixed $value
    *
    * @return $this
    */
    public function setValue($value)
    {
        $this->setContent($value);
        return $this;
    }

   /**
    * Static elements have no value
    *
    * @return    null
    */
    public function getRawValue()
    {
        return null;
    }

    public function __toString()
    {
        $prefix = $this->getIndent();
        if ($comment = $this->getComment()) {
            $prefix .= '<!-- ' . $comment . ' -->'
                       . HTML_Common2::getOption('linebreak') . $this->getIndent();
        }

        if (!$this->tagName) {
            return $prefix . $this->getContent();
        } elseif ('' != $this->getContent()) {
            return $prefix . '<' . $this->tagName . $this->getAttributes(true)
                   . '>' . $this->getContent() . '</' . $this->tagName . '>';
        } else {
            return $prefix . '<' . $this->tagName . $this->getAttributes(true)
                   . ($this->forceClosingTag ? '></' . $this->tagName . '>' : ' />');
        }
    }

    public function getJavascriptValue($inContainer = false)
    {
        return '';
    }

    public function getJavascriptTriggers()
    {
        return array();
    }

   /**
    * Called when the element needs to update its value from form's data sources
    *
    * Static elements content can be updated with default form values.
    */
    protected function updateValue()
    {
        $name = $this->getName();
        /* @var $ds HTML_QuickForm2_DataSource_NullAware */
        foreach ($this->getDataSources() as $ds) {
            if (!$ds instanceof HTML_QuickForm2_DataSource_Submit
                && (null !== ($value = $ds->getValue($name))
                    || $ds instanceof HTML_QuickForm2_DataSource_NullAware && $ds->hasValue($name))
            ) {
                $this->setContent($value);
                return;
            }
        }
    }

   /**
    * Sets the name of the HTML tag to wrap around static element's content
    *
    * @param string $name         tag name
    * @param bool   $forceClosing whether to output closing tag in case of empty contents
    *
    * @throws HTML_QuickForm2_Exception_InvalidArgument when trying to set a tag
    *       name corresponding to a form element
    * @return $this
    */
    public function setTagName($name, $forceClosing = true)
    {
        // Prevent people shooting themselves in the proverbial foot
        if (in_array(strtolower($name),
                     array('form', 'fieldset', 'button', 'input', 'select', 'textarea'))
        ) {
            throw new HTML_QuickForm2_Exception_InvalidArgument(
                "Do not use tag name '{$name}' with Static element, use proper element class"
            );
        }
        $this->tagName         = (string)$name;
        $this->forceClosingTag = (bool)$forceClosing;

        return $this;
    }
}
?>