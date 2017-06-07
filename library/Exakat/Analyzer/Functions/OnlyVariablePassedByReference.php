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
use Exakat\Data\Methods;

class OnlyVariablePassedByReference extends Analyzer {
    public function analyze() {
        // Functioncalls
        $this->atomIs('Functioncall')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->atomIsNot(array('Variable', 'Property', 'Staticproperty', 'Array'))
             ->savePropertyAs('rank', 'position')
             ->back('first')
             ->functionDefinition()
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->samePropertyAs('rank', 'position')
             ->is('reference', true)
             ->back('first');
        $this->prepareQuery();

        // Static methods calls
        $this->atomIs('Staticmethodcall')
             ->outIs('METHOD')
             ->tokenIs('T_STRING')
             ->savePropertyAs('code', 'method')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->atomIsNot(array('Variable', 'Property', 'Staticproperty', 'Array'))
             ->savePropertyAs('rank', 'position')
             ->back('first')
             ->outIs('CLASS')
             ->classDefinition()
             ->outIs('METHOD')
             ->atomIs('Method')
             ->samePropertyAs('code', 'method')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->samePropertyAs('rank', 'position')
             ->is('reference', true)
             ->back('first');
        $this->prepareQuery();

        $methods = new Methods($this->config);
        $functions = $methods->getFunctionsReferenceArgs();
        $references = array();
        
        foreach($functions as $function) {
            if (!isset($references[$function['position']])) {
                $references[$function['position']] = array('\\'.$function['function']);
            } else {
                $references[$function['position']][] = '\\'.$function['function'];
            }
        }

        foreach($references as $position => $functions) {
            // Functioncalls
            $this->atomIs('Functioncall')
                 ->fullnspathIs($functions)
                 ->outIs('ARGUMENTS')
                 ->outWithRank('ARGUMENT', $position)
                 ->atomIsNot(array('Variable', 'Property', 'Staticproperty', 'Array'))
                 ->back('first');
            $this->prepareQuery();
        }
    }
}

?>
