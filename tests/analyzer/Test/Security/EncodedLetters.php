<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Security_EncodedLetters extends Analyzer {
    /* 3 methods */

    public function testSecurity_EncodedLetters01()  { $this->generic_test('Security/EncodedLetters.01'); }
    public function testSecurity_EncodedLetters02()  { $this->generic_test('Security/EncodedLetters.02'); }
    public function testSecurity_EncodedLetters03()  { $this->generic_test('Security/EncodedLetters.03'); }
}
?>