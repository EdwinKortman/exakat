<?php

namespace Test;

include_once(dirname(dirname(dirname(__DIR__))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Boolean extends Tokenizer {
    /* 5 methods */
    public function testBoolean01()  { $this->generic_test('Boolean.01'); }
    public function testBoolean02()  { $this->generic_test('Boolean.02'); }
    public function testBoolean03()  { $this->generic_test('Boolean.03'); }
    public function testBoolean04()  { $this->generic_test('Boolean.04'); }
    public function testBoolean05()  { $this->generic_test('Boolean.05'); }
}
?>