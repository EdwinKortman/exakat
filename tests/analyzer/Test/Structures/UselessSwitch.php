<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Structures_UselessSwitch extends Analyzer {
    /* 3 methods */

    public function testStructures_UselessSwitch01()  { $this->generic_test('Structures/UselessSwitch.01'); }
    public function testStructures_UselessSwitch02()  { $this->generic_test('Structures/UselessSwitch.02'); }
    public function testStructures_UselessSwitch03()  { $this->generic_test('Structures/UselessSwitch.03'); }
}
?>