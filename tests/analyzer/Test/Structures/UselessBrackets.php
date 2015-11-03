<?php

namespace Test;

include_once(dirname(dirname(dirname(__DIR__))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Structures_UselessBrackets extends Analyzer {
    /* 5 methods */

    public function testStructures_UselessBrackets01()  { $this->generic_test('Structures_UselessBrackets.01'); }
    public function testStructures_UselessBrackets02()  { $this->generic_test('Structures_UselessBrackets.02'); }
    public function testStructures_UselessBrackets03()  { $this->generic_test('Structures_UselessBrackets.03'); }
    public function testStructures_UselessBrackets04()  { $this->generic_test('Structures_UselessBrackets.04'); }
    public function testStructures_UselessBrackets05()  { $this->generic_test('Structures_UselessBrackets.05'); }
}
?>