<?php

/** Sets up includes */
require_once dirname(dirname(dirname(__FILE__))) . '/TestHelper.php';

class HTML_QuickForm2_MessageProvider_StrftimeTest extends PHPUnit\Framework\TestCase
{
    function testNonDate()
    {
        $this->expectException('HTML_QuickForm2_Exception_InvalidArgument');
        $object=new HTML_QuickForm2_MessageProvider_Strftime();
        $object->get([]);
    }

    function testDate()
    {
        $object=new HTML_QuickForm2_MessageProvider_Strftime();
        $d=$object->get(['date', 'weekdays_long', 5]);
        $this->assertIsString($d);
        $d=$object->get(['date', 'weekdays_long', 50]);
        $this->assertNull($d);

    }
}
