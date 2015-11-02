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


namespace Analyzer\Functions;

use Analyzer;

class UsedFunctions extends Analyzer\Analyzer {
    public function dependsOn() {
        return array('Functions/MarkCallable');
    }
    
    public function analyze() {
        // function used
        $functions = $this->query(<<<GREMLIN
g.idx("atoms")[["atom":"Functioncall"]].hasNot("fullnspath", null).fullnspath.unique()
GREMLIN
);
        if (!empty($functions)) {
            $this->atomIs('Function')
                 ->raw('filter{ it.in("ELEMENT").in("BLOCK").has("atom", "Class").any() == false}')
                 ->raw('filter{it.out("NAME").next().code != ""}')
                 ->outIs('NAME')
                 ->fullnspath($functions);
            $this->prepareQuery();
        }

        // function name used in a string
        $functionsInStrings = $this->query(<<<GREMLIN
g.idx("atoms")[["atom":"String"]].hasNot("fullnspath", null).fullnspath.unique()
GREMLIN
);
        if (!empty($functionsInStrings)) {
            $this->atomIs('Function')
                 ->outIs('NAME')
                 ->fullnspath($functionsInStrings);
            $this->prepareQuery();
        }
    }
}

?>
