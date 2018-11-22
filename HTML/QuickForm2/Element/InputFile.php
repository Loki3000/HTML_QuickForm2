<?php
/**
 * Class for <input type="file" /> elements
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
 * Class for <input type="file" /> elements
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_InputFile extends HTML_QuickForm2_Element_Input
{
    /**
     * Language to display error messages in
     * @var  string
     */
    protected $language = null;

    /**
     * Information on uploaded file, from submit data source
     * @var array
     */
    protected $value = null;

    /**
     * One level uploaded file array
     * @var array
     */
    protected $valueArray = array();

    protected $attributes = array('type' => 'file');

    /**
     * Message provider for upload error messages
     * @var  callback|HTML_QuickForm2_MessageProvider
     */
    protected $messageProvider;

    /**
     * Class constructor
     *
     * Possible keys in $data array are:
     *  - 'messageProvider': an instance of a class implementing
     *    HTML_QuickForm2_MessageProvider interface, this will be used to get
     *    localized error messages. Default will be used if not given.
     *  - 'language': language to display error messages in, will be passed to
     *    message provider.
     *
     * @param string       $name       Element name
     * @param string|array $attributes Attributes (either a string or an array)
     * @param array        $data       Data used to set up error messages for PHP's
     *                                 file upload errors.
     */
    public function __construct($name = null, $attributes = null, array $data = array())
    {
        if (isset($data['messageProvider'])) {
            if (!is_callable($data['messageProvider'])
                && !$data['messageProvider'] instanceof HTML_QuickForm2_MessageProvider
            ) {
                throw new HTML_QuickForm2_Exception_InvalidArgument(
                    "messageProvider: expecting a callback or an implementation"
                    . " of HTML_QuickForm2_MessageProvider"
                );
            }
            $this->messageProvider = $data['messageProvider'];
        } else {
            HTML_QuickForm2_Loader::loadClass('HTML_QuickForm2_MessageProvider_Default');
            $this->messageProvider = HTML_QuickForm2_MessageProvider_Default::getInstance();
        }
        if (isset($data['language'])) {
            $this->language = $data['language'];
        }
        unset($data['messageProvider'], $data['language']);
        parent::__construct($name, $attributes, $data);
    }

    /**
     * File upload elements cannot be frozen
     *
     * To properly "freeze" a file upload element one has to store the uploaded
     * file somewhere and store the file info in session. This is way outside
     * the scope of this class.
     *
     * @param bool $freeze Whether element should be frozen or editable. This
     *                     parameter is ignored in case of file uploads
     *
     * @return   bool    Always returns false
     */
    public function toggleFrozen($freeze = null)
    {
        return false;
    }

    /**
     * Returns the information on uploaded file
     *
     * @return   array|null
     */
    public function getRawValue()
    {
        return $this->value;
    }

    /**
     * Alias of getRawValue(), InputFile elements do not allow filters
     *
     * @return   array|null
     */
    public function getValue()
    {
        return $this->getRawValue();
    }

    public function getValueArray()
    {
        return $this->valueArray;
    }

    /**
     * File upload's value cannot be set here
     *
     * @param mixed $value Value for file element, this parameter is ignored
     *
     * @return $this
     */
    public function setValue($value)
    {
        return $this;
    }

    protected function updateValue()
    {
        // request #16807: file uploads should not be added to forms with
        // method="get", enctype should be set to multipart/form-data
        // we cannot do this in setContainer() as the element may be added to
        // e.g. a group first and then the group may be added to a form
        $container = $this->getContainer();
        while (!empty($container)) {
            if ($container instanceof HTML_QuickForm2) {
                if ('get' == $container->getAttribute('method')) {
                    throw new HTML_QuickForm2_Exception_InvalidArgument(
                        'File upload elements can only be added to forms with post submit method'
                    );
                }
                if ('multipart/form-data' != $container->getAttribute('enctype')) {
                    $container->setAttribute('enctype', 'multipart/form-data');
                }
                break;
            }
            $container = $container->getContainer();
        }

        foreach ($this->getDataSources() as $ds) {
            if ($ds instanceof HTML_QuickForm2_DataSource_Submit) {
                $value = $ds->getUpload($this->getName());
                if (null !== $value) {
                    $this->value = $value;
                    $this->setValueArray($this->value);
                    return;
                }
            }
        }
        $this->value = null;
    }

    protected function setValueArray($data)
    {
        if (array_key_exists('name', $data) && array_key_exists('tmp_name', $data)) {
            if (isset($data['error']) && UPLOAD_ERR_NO_FILE != $data['error']) {
                $this->valueArray[]=$data;
            }
            return;
        }
        foreach ($data as $k => $v) {
            $this->setValueArray($v);
        }
    }

    /**
     * Performs the server-side validation
     *
     * Before the Rules added to the element kick in, the element checks the
     * error code added to the $_FILES array by PHP. If the code isn't
     * UPLOAD_ERR_OK or UPLOAD_ERR_NO_FILE then a built-in error message will be
     * displayed and no further validation will take place.
     *
     * @return   boolean     Whether the element is valid
     */
    protected function validate()
    {
        foreach ($this->getValueArray() as $value) {
            if (!$this->fileValidate($value)) {
                return false;
            }
        }
        return parent::validate();
    }

    protected function fileValidate($file)
    {
        if (strlen($this->error)) {
            return false;
        }
        if (isset($file['error'])
            && !in_array($file['error'], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))
        ) {
            $errorMessage = $this->messageProvider instanceof HTML_QuickForm2_MessageProvider
                            ? $this->messageProvider->get(array('file', $file['error']), $this->language)
                            : call_user_func($this->messageProvider, array('file', $file['error']), $this->language);
            if (UPLOAD_ERR_INI_SIZE == $file['error']) {
                $iniSize = ini_get('upload_max_filesize');
                $size    = intval($iniSize);
                switch (strtoupper(substr($iniSize, -1))) {
                    case 'G':
                        $size *= 1024;
                        // no break
                    case 'M':
                        $size *= 1024;
                        // no break
                    case 'K':
                        $size *= 1024;
                }
            } elseif (UPLOAD_ERR_FORM_SIZE == $file['error']) {
                foreach ($this->getDataSources() as $ds) {
                    if ($ds instanceof HTML_QuickForm2_DataSource_Submit) {
                        $size = intval($ds->getValue('MAX_FILE_SIZE'));
                        break;
                    }
                }
            }
            $this->error = isset($size)? sprintf($errorMessage, $size): $errorMessage;
            return false;
        }
        return true;
    }

    public function addFilter($callback, array $options = array())
    {
        throw new HTML_QuickForm2_Exception(
            'InputFile elements do not support filters'
        );
    }

    public function addRecursiveFilter($callback, array $options = array())
    {
        throw new HTML_QuickForm2_Exception(
            'InputFile elements do not support filters'
        );
    }
}
