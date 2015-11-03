<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Spip_ChargerFonction extends Analyzer {
    /* 1 methods */

    public function testSpip_ChargerFonction01()  { $this->generic_test('Spip_ChargerFonction.01'); }
}
?>