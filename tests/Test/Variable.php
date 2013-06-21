<?php

namespace Test;

include_once(dirname(dirname(__DIR__)).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');

class Variable extends Tokenizeur {
    /* 22 methods */

    public function testVariable01()  { $this->generic_test('Variable.01'); }
    public function testVariable02()  { $this->generic_test('Variable.02'); }
    public function testVariable03()  { $this->generic_test('Variable.03'); }
    public function testVariable04()  { $this->generic_test('Variable.04'); }
    public function testVariable05()  { $this->generic_test('Variable.05'); }
    public function testVariable06()  { $this->generic_test('Variable.06'); }
    public function testVariable07()  { $this->generic_test('Variable.07'); }
    public function testVariable08()  { $this->generic_test('Variable.08'); }
    public function testVariable09()  { $this->generic_test('Variable.09'); }
    public function testVariable10()  { $this->generic_test('Variable.10'); }
    public function testVariable11()  { $this->generic_test('Variable.11'); }
    public function testVariable12()  { $this->generic_test('Variable.12'); }
    public function testVariable13()  { $this->generic_test('Variable.13'); }
    public function testVariable14()  { $this->generic_test('Variable.14'); }
    public function testVariable15()  { $this->generic_test('Variable.15'); }
    public function testVariable16()  { $this->generic_test('Variable.16'); }
    public function testVariable17()  { $this->generic_test('Variable.17'); }
    public function testVariable18()  { $this->generic_test('Variable.18'); }
    public function testVariable19()  { $this->generic_test('Variable.19'); }
    public function testVariable20()  { $this->generic_test('Variable.20'); }
    public function testVariable21()  { $this->generic_test('Variable.21'); }
    public function testVariable22()  { $this->generic_test('Variable.22'); }
}
?>
