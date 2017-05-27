<?php

require_once 'HTML/QuickForm2/Element/Input.php';

class HTML_QuickForm2_Element_InputTel extends HTML_QuickForm2_Element_Input
{
    protected $persistent = true;

    protected $attributes = array('type' => 'tel');
}
?>

