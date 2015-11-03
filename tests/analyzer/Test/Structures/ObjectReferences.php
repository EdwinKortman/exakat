<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Structures_ObjectReferences extends Analyzer {
    /* 3 methods */

    public function testStructures_ObjectReferences01()  { $this->generic_test('Structures_ObjectReferences.01'); }
    public function testStructures_ObjectReferences02()  { $this->generic_test('Structures_ObjectReferences.02'); }
    public function testStructures_ObjectReferences03()  { $this->generic_test('Structures_ObjectReferences.03'); }
}
?>