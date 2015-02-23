<?php
/*
 * Copyright 2012-2015 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/


namespace Analyzer\Interfaces;

use Analyzer;

class IsExtInterface extends Analyzer\Analyzer {

    public function dependsOn() {
        return array("Analyzer\\Interfaces\\InterfaceUsage");
    }
    
    public function analyze() {
        $exts = self::$docs->listAllAnalyzer('Extensions');
        $exts[] = 'php_interfaces';
        
        $interfaces = array();
        foreach($exts as $ext) {
            $inifile = str_replace('Extensions\Ext', '', $ext).'.ini';
            $ini = $this->loadIni($inifile);
            
            if (!empty($ini['classes'][0])) {
                $interfaces = array_merge($interfaces, $ini['interfaces']);
            }
        }

        $interfaces = $this->makeFullNsPath($interfaces);
        
        $this->analyzerIs("Analyzer\\Interfaces\\InterfaceUsage")
             ->fullnspath($interfaces);
        $this->prepareQuery();
    }
}

?>
