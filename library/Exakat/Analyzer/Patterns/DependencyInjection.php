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

namespace Exakat\Analyzer\Patterns;

use Exakat\Analyzer\Analyzer;

class DependencyInjection extends Analyzer {
    public function dependsOn() {
        return array('Classes/Constructor');
    }
    
    public function analyze() {
        // Assigned to a property at constructor
        $this->atomIs('Method')
             ->analyzerIs('Classes/Constructor')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->_as('result')
             ->outIs('TYPEHINT')
             ->fullnspathIsNot(array('\callable', '\array', '\string', '\int', '\float', '\bool'))
             ->inIs('TYPEHINT')
             ->outIsIE('LEFT')
             ->savePropertyAs('code', 'arg')
             ->back('first')
             ->outIs('BLOCK')
             ->atomInside('Variable')
             ->samePropertyAs('code', 'arg')
             ->inIs('RIGHT')
             ->atomIs('Assignation')
             ->outIs('LEFT')
             ->atomIs('Property')
             ->outIs('OBJECT')
             ->codeIs('$this')
             ->back('result');
        $this->prepareQuery();

        // Assigned to a property at constructor
        $this->atomIs('Method')
             ->analyzerIs('Classes/Constructor')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->_as('result')
             ->outIs('TYPEHINT')
             ->fullnspathIsNot(array('\callable', '\array', '\string', '\int', '\float', '\bool'))
             ->inIs('TYPEHINT')
             ->outIsIE('LEFT')
             ->savePropertyAs('code', 'arg')
             ->back('first')
             ->inIs('METHOD')
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')
             ->outIs('BLOCK')
             ->atomInside('Variable')
             ->samePropertyAs('code', 'arg')
             ->inIs('RIGHT')
             ->atomIs('Assignation')
             ->outIs('LEFT')
             ->atomIs('Staticproperty')
             ->outIs('CLASS')
             ->samePropertyAs('fullnspath', 'fnp')
             ->back('result');
        $this->prepareQuery();

    }
}

?>
