<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Functions_Dynamiccall extends Analyzer {
    /* 1 methods */

    public function testFunctions_Dynamiccall01()  { $this->generic_test('Functions_Dynamiccall.01'); }
}
?>