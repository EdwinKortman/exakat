<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Functions_RelayFunction extends Analyzer {
    /* 2 methods */

    public function testFunctions_RelayFunction01()  { $this->generic_test('Functions/RelayFunction.01'); }
    public function testFunctions_RelayFunction02()  { $this->generic_test('Functions/RelayFunction.02'); }
}
?>