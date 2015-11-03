<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Php_debugInfoUsage extends Analyzer {
    /* 2 methods */

    public function testPhp_debugInfoUsage01()  { $this->generic_test('Php_debugInfoUsage.01'); }
    public function testPhp_debugInfoUsage02()  { $this->generic_test('Php_debugInfoUsage.02'); }
}
?>