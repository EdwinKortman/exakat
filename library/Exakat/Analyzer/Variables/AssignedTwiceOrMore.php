<?php
/*
 * Copyright 2012-2017 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
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

namespace Exakat\Analyzer\Variables;

use Exakat\Analyzer\Analyzer;

class AssignedTwiceOrMore extends Analyzer {
    public function analyze() {
        $query = 'g.V().hasLabel("Function").where( 
            __.sideEffect{counts = [:]; names = [];}
              .out("BLOCK").repeat( __.out('.$this->linksDown.')).emit(hasLabel("Assignation")).times('.self::MAX_LOOPING.')
              .hasLabel("Assignation").has("code", "=")
              .out("RIGHT").hasLabel("Integer", "Real", "Boolean", "Null", "Herdoc").in("RIGHT")
              .out("LEFT").hasLabel("Variable")
              .sideEffect{ k = it.get().value("fullcode"); 
                           if (counts[k] == null) {
                              counts[k] = 1;
                           } else {
                              counts[k]++;
                           }
                        }
                .fold()
            )
           .sideEffect{ names = counts.findAll{ a,b -> b > 1}.keySet() }
           .filter{ names.size() > 0;}
           .map{ ["key":it.get().value("code"),"value":names]; }';
        $variables = $this->queryHash($query, null, 'fullcode', 'variables');
        
        $this->atomIs('Function')
             ->savePropertyAs('code', 'name')
             ->outIs('BLOCK')
             ->atomInside('Assignation')
             ->outIs('LEFT')
             ->atomIs('Variable')
             ->isHash('code', $variables, 'name');
        $this->prepareQuery();
    }
}

?>
