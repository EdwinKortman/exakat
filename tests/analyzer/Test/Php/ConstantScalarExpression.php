<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Php_ConstantScalarExpression extends Analyzer {
    /* 2 methods */

    public function testPhp_ConstantScalarExpression01()  { $this->generic_test('Php_ConstantScalarExpression.01'); }
    public function testPhp_ConstantScalarExpression02()  { $this->generic_test('Php_ConstantScalarExpression.02'); }
}
?>