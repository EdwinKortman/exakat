<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Structures_NoParenthesisForLanguageConstruct extends Analyzer {
    /* 1 methods */

    public function testStructures_NoParenthesisForLanguageConstruct01()  { $this->generic_test('Structures_NoParenthesisForLanguageConstruct.01'); }
}
?>