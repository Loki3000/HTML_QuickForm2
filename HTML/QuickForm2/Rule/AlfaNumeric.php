<?php

require_once 'HTML/QuickForm2/Rule.php';

class HTML_QuickForm2_Rule_AlfaNumeric extends HTML_QuickForm2_Rule
{
    protected function validateOwner()
    {
        $value = $this->owner->getValue();
        
        return ctype_alnum($value);
    }
}
?>
