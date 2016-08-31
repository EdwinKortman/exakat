<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Variables_RealVariables extends Analyzer {
    /* 2 methods */

    public function testVariables_RealVariables01()  { $this->generic_test('Variables/RealVariables.01'); }
    public function testVariables_RealVariables02()  { $this->generic_test('Variables/RealVariables.02'); }
}
?>