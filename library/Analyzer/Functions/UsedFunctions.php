<?php

namespace Analyzer\Functions;

use Analyzer;

class UsedFunctions extends Analyzer\Analyzer {
    public function dependsOn() {
//        return array('Analyzer\\Constants\\CustomConstantUsage');
    }
    
    public function analyze() {
        $this->atomIs("Functioncall")
             ->hasNoIn('METHOD')
             ->hasFunctionDefinition();
        $this->prepareQuery();
    }
}

?>