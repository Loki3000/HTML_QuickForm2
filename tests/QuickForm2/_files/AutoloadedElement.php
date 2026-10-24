<?php
/**
 * Element class living outside the PEAR naming scheme, loadable only via autoloader
 */

namespace QuickForm2Fixture;

class AutoloadedElement
{
    public $name;
    public $data;
    public $attributes;

    public function __construct($name = null, $attributes = null, $data = null)
    {
        $this->name         = $name;
        $this->data         = $data;
        $this->attributes   = $attributes;
    }
}
