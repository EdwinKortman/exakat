<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Classes_UnusedClass extends Analyzer {
    /* 3 methods */

    public function testClasses_UnusedClass01()  { $this->generic_test('Classes_UnusedClass.01'); }
    public function testClasses_UnusedClass02()  { $this->generic_test('Classes_UnusedClass.02'); }
    public function testClasses_UnusedClass03()  { $this->generic_test('Classes_UnusedClass.03'); }
}
?>