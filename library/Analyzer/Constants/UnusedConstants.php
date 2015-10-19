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


namespace Analyzer\Constants;

use Analyzer;

class UnusedConstants extends Analyzer\Analyzer {
    public function dependsOn() {
        return array('Constants/ConstantUsage');
    }
    
    public function analyze() {
        // Const from a define
        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
             ->fullnspath('\define')
             ->outIs('ARGUMENTS')
             ->rankIs('ARGUMENT', 'first')
             ->atomIs('String')
             ->raw('filter{ name = it.noDelimiter; g.idx("analyzers")[["analyzer":"Analyzer\\\\Constants\\\\ConstantUsage"]].out("ANALYZED").filter{it.code.toLowerCase() == name}.any() == false }');
        $this->prepareQuery();

        // Const from a const
        $this->atomIs('Const')
             ->hasNoClass()
             ->outIs('CONST')
             ->outIs('LEFT')
             ->raw('filter{ name = it.code.toLowerCase(); g.idx("analyzers")[["analyzer":"Analyzer\\\\Constants\\\\ConstantUsage"]].out("ANALYZED").filter{it.code.toLowerCase() == name}.any() == false }');
        $this->prepareQuery();
      }
}

?>
