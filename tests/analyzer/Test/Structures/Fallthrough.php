<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Structures_Fallthrough extends Analyzer {
    /* 4 methods */

    public function testStructures_Fallthrough01()  { $this->generic_test('Structures/Fallthrough.01'); }
    public function testStructures_Fallthrough02()  { $this->generic_test('Structures/Fallthrough.02'); }
    public function testStructures_Fallthrough03()  { $this->generic_test('Structures/Fallthrough.03'); }
    public function testStructures_Fallthrough04()  { $this->generic_test('Structures/Fallthrough.04'); }
}
?>