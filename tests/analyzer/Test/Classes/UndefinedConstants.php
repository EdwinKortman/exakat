<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Classes_UndefinedConstants extends Analyzer {
    /* 5 methods */

    public function testClasses_UndefinedConstants01()  { $this->generic_test('Classes_UndefinedConstants.01'); }
    public function testClasses_UndefinedConstants02()  { $this->generic_test('Classes_UndefinedConstants.02'); }
    public function testClasses_UndefinedConstants03()  { $this->generic_test('Classes_UndefinedConstants.03'); }
    public function testClasses_UndefinedConstants04()  { $this->generic_test('Classes_UndefinedConstants.04'); }
    public function testClasses_UndefinedConstants05()  { $this->generic_test('Classes/UndefinedConstants.05'); }
}
?>