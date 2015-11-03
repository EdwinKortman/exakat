<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Classes_PropertyDefinition extends Analyzer {
    /* 1 methods */

    public function testClasses_PropertyDefinition01()  { $this->generic_test('Classes_PropertyDefinition.01'); }
    public function testClasses_PropertyDefinition02()  { $this->generic_test('Classes_PropertyDefinition.02'); }
}
?>