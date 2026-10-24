<?php
/**
 * Rule checking that uploaded file size does not exceed the given limit
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
 * Rule checking that uploaded file size does not exceed the given limit
 *
 * The Rule needs one configuration parameter for its work: the size limit.
 * This limit can be passed either to
 * {@link HTML_QuickForm2_Rule::__construct() the Rule constructor} as local
 * configuration or to {@link HTML_QuickForm2_Factory::registerRule()} as
 * global one. As usual, global configuration overrides local one.
 *
 * Note that if file upload failed due to upload_max_filesize php.ini setting
 * or MAX_FILE_SIZE form field, then this rule won't even be called, due to
 * File element's built-in validation setting the error message.
 *
 * The Rule considers missing file uploads (UPLOAD_ERR_NO_FILE) valid.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 *
 * @extends HTML_QuickForm2_Rule<HTML_QuickForm2_Element_InputFile>
 */
class HTML_QuickForm2_Rule_MaxFileSize extends HTML_QuickForm2_Rule
{
   /**
    * Validates the owner element
    *
    * @return   bool    whether uploaded file's size is within given limit
    */
    protected function validateOwner()
    {
        foreach ($this->owner->getValueArray() as $value) {
            if ($this->getConfig() < @filesize($value['tmp_name'])) {
                return false;
            }
        }
        return true;
    }

   /**
    * Sets maximum allowed file size
    *
    * @param int $config Maximum allowed size
    *
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_Exception_InvalidArgument    if a bogus size limit was provided
    */
    public function setConfig($config)
    {
        if (0 >= $config) {
            throw new HTML_QuickForm2_Exception_InvalidArgument(
                'MaxFileSize Rule requires a positive size limit, ' .
                preg_replace('/\s+/', ' ', var_export($config, true)) . ' given'
            );
        }
        return parent::setConfig($config);
    }

   /**
    * Sets the element that will be validated by this rule
    *
    * @param HTML_QuickForm2_Node $owner File upload field to validate
    *
    * @throws   HTML_QuickForm2_Exception_InvalidArgument    if trying to use
    *           this Rule on something that isn't a file upload field
    */
    public function setOwner(HTML_QuickForm2_Node $owner)
    {
        if (!$owner instanceof HTML_QuickForm2_Element_InputFile) {
            throw new HTML_QuickForm2_Exception_InvalidArgument(
                'MaxFileSize Rule can only validate file upload fields, '.
                get_class($owner) . ' given'
            );
        }
        parent::setOwner($owner);
    }
}
?>
