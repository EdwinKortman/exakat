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

namespace Exakat\Analyzer\Functions;

use Exakat\Analyzer\Analyzer;

class CouldBeCallable extends Analyzer {
    public function dependsOn() {
        return array('Functions/MarkCallable');
    }
    
    public function analyze() {
        // $arg(...)
        $this->atomIs(self::$FUNCTION_METHOD)
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->raw('not(where( __.out("TYPEHINT").has("token", "T_CALLABLE")))')
             ->_as('results')
             ->outIsIE('LEFT')
             ->savePropertyAs('code', 'argument')
             ->back('first')
             ->outIs('BLOCK')
             ->atomInside('Variable')
             ->samePropertyAs('code', 'argument')
             ->inIs('NAME')
             ->atomIs('Functioncall')
             ->back('first');
        $this->prepareQuery();

        // array_map($array, $arg)
        $this->atomIs(self::$FUNCTION_METHOD)
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->raw('not(where( __.out("TYPEHINT").has("token", "T_CALLABLE")))')
             ->_as('results')
             ->outIsIE('LEFT')
             ->savePropertyAs('code', 'argument')
             ->back('first')
             ->outIs('BLOCK')
             ->atomInside('Variable')
             ->samePropertyAs('code', 'argument')
             ->analyzerIs('Functions/MarkCallable')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
