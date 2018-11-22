<?php
/**
 * Base class for <input> elements
 */

class HTML_QuickForm2_Element_InputEmail extends HTML_QuickForm2_Element_Input
{
    protected $persistent = true;

    protected $attributes = array('type' => 'email');
}
