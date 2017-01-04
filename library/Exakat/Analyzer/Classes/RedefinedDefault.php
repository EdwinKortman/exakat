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
namespace Exakat\Analyzer\Classes;

use Exakat\Analyzer\Analyzer;

class RedefinedDefault extends Analyzer {
    public function analyze() {
        $this->atomIs('Class')
             ->outIs('BLOCK')
             ->outIs('ELEMENT')
             ->atomIs('Ppp')
             ->outIs('PPP')
             ->savePropertyAs('propertyname', 'name')
             ->inIsIE('LEFT')
             ->_as('results')
             ->inIs('PPP')
             ->inIs('ELEMENT')

             ->outIs('ELEMENT')
             ->atomIs('Function')
             ->outIs('NAME')
             ->codeIs('__construct')
             ->inIs('NAME')
             ->outIs('BLOCK')
             ->outIs('ELEMENT')
// Not using atomInside, to avoid values in a condition
//             ->atomInside('Assignation') 
             ->atomIs('Assignation')
             ->codeIs('=')
             ->outIs('LEFT')
             ->atomIs('Property')
             ->outIs('OBJECT')
             ->codeIs('$this')
             ->inIs('OBJECT')
             ->outIs('PROPERTY')
             ->samePropertyAs('code', 'name')
             
             // sameParameterAs
             ->back('results');
        $this->prepareQuery();
    }
}

?>
