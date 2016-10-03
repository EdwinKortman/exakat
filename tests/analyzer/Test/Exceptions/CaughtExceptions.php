<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Exceptions_CaughtExceptions extends Analyzer {
    /* 2 methods */

    public function testExceptions_CaughtExceptions01()  { $this->generic_test('Exceptions_CaughtExceptions.01'); }
    public function testExceptions_CaughtExceptions02()  { $this->generic_test('Exceptions/CaughtExceptions.02'); }
}
?>