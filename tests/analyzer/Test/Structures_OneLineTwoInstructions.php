<?php

namespace Test;

include_once(dirname(dirname(dirname(__DIR__))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Structures_OneLineTwoInstructions extends Analyzer {
    /* 4 methods */

    public function testStructures_OneLineTwoInstructions01()  { $this->generic_test('Structures_OneLineTwoInstructions.01'); }
    public function testStructures_OneLineTwoInstructions02()  { $this->generic_test('Structures_OneLineTwoInstructions.02'); }
    public function testStructures_OneLineTwoInstructions03()  { $this->generic_test('Structures_OneLineTwoInstructions.03'); }
    public function testStructures_OneLineTwoInstructions04()  { $this->generic_test('Structures_OneLineTwoInstructions.04'); }
}
?>