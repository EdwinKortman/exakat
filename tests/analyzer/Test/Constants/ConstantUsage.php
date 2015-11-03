<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Constants_ConstantUsage extends Analyzer {
    /* 5 methods */

    public function testConstants_ConstantUsage01()  { $this->generic_test('Constants_ConstantUsage.01'); }
    public function testConstants_ConstantUsage02()  { $this->generic_test('Constants_ConstantUsage.02'); }
    public function testConstants_ConstantUsage03()  { $this->generic_test('Constants_ConstantUsage.03'); }
    public function testConstants_ConstantUsage04()  { $this->generic_test('Constants_ConstantUsage.04'); }
    public function testConstants_ConstantUsage05()  { $this->generic_test('Constants_ConstantUsage.05'); }
}
?>