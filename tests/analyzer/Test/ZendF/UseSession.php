<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class ZendF_UseSession extends Analyzer {
    /* 2 methods */

    public function testZendF_UseSession01()  { $this->generic_test('ZendF/UseSession.01'); }
    public function testZendF_UseSession02()  { $this->generic_test('ZendF/UseSession.02'); }
}
?>