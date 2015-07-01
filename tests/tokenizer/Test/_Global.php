<?php

namespace Test;

include_once(dirname(dirname(dirname(__DIR__))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class _Global extends Tokenizer {
    /* 12 methods */

    public function test_Global01()  { $this->generic_test('_Global.01'); }
    public function test_Global02()  { $this->generic_test('_Global.02'); }
    public function test_Global03()  { $this->generic_test('_Global.03'); }
    public function test_Global04()  { $this->generic_test('_Global.04'); }
    public function test_Global05()  { $this->generic_test('_Global.05'); }
    public function test_Global06()  { $this->generic_test('_Global.06'); }
    public function test_Global07()  { $this->generic_test('_Global.07'); }
    public function test_Global08()  { $this->generic_test('_Global.08'); }
    public function test_Global09()  { $this->generic_test('_Global.09'); }
    public function test_Global10()  { $this->generic_test('_Global.10'); }
    public function test_Global11()  { $this->generic_test('_Global.11'); }
    public function test_Global12()  { $this->generic_test('_Global.12'); }
}
?>