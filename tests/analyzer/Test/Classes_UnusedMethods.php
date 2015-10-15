<?php

namespace Test;

include_once(dirname(dirname(dirname(__DIR__))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Classes_UnusedMethods extends Analyzer {
    /* 5 methods */

    public function testClasses_UnusedMethods01()  { $this->generic_test('Classes_UnusedMethods.01'); }
    public function testClasses_UnusedMethods02()  { $this->generic_test('Classes_UnusedMethods.02'); }
    public function testClasses_UnusedMethods03()  { $this->generic_test('Classes_UnusedMethods.03'); }
    public function testClasses_UnusedMethods04()  { $this->generic_test('Classes_UnusedMethods.04'); }
    public function testClasses_UnusedMethods05()  { $this->generic_test('Classes_UnusedMethods.05'); }
}
?>