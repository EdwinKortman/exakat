<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Security_parseUrlWithoutParameters extends Analyzer {
    /* 2 methods */

    public function testSecurity_parseUrlWithoutParameters01()  { $this->generic_test('Security_parseUrlWithoutParameters.01'); }
    public function testSecurity_parseUrlWithoutParameters02()  { $this->generic_test('Security/parseUrlWithoutParameters.02'); }
}
?>