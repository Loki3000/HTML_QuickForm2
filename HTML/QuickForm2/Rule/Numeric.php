<?php

require_once 'HTML/QuickForm2/Rule.php';

class HTML_QuickForm2_Rule_Numeric extends HTML_QuickForm2_Rule
{
    protected function validateOwner()
    {
        $value = trim($this->owner->getValue());
        
        return is_numeric($value) || empty($value);
    }
}
?>

