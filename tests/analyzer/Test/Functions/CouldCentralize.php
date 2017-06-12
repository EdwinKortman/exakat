<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Functions_CouldCentralize extends Analyzer {
    /* 3 methods */

    public function testFunctions_CouldCentralize01()  { $this->generic_test('Functions/CouldCentralize.01'); }
    public function testFunctions_CouldCentralize02()  { $this->generic_test('Functions/CouldCentralize.02'); }
    public function testFunctions_CouldCentralize03()  { $this->generic_test('Functions/CouldCentralize.03'); }
}
?>