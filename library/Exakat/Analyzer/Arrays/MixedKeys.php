<?php
/*
 * Copyright 2012-2016 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
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


namespace Exakat\Analyzer\Arrays;

use Exakat\Analyzer\Analyzer;

class MixedKeys extends Analyzer {
    protected $phpVersion = '5.6+';
    
    public function analyze() {
        // build with array()
        $this->atomIs('Ppp')
             ->outIs('PPP')
             ->atomInside('Functioncall')
             ->functioncallIs('\\array')
             ->_as('result')

             // count keys styles
             ->raw('where(
   __.sideEffect{ counts = [:]; }
      .out("ARGUMENTS").out("ARGUMENT").hasLabel("Keyvalue").out("KEY")
      .hasLabel("String", "Integer", "Real", "Boolean", "Staticconstant", "Identifier").where(__.out("CONCAT").count().is(eq(0)))
      .sideEffect{ 
            if (it.get().label() in ["Identifier", "Staticconstant"] ) { k = "a"; } else { k = "b"; }
            if (counts[k] == null) { counts[k] = 1; } else { counts[k]++; }
        }
        .map{ counts.size(); }.is(eq(2))
)')
              ->back('result');
        $this->prepareQuery();
    }
}

?>
