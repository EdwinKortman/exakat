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


namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;

class DoubleAssignation extends Analyzer {
    public function analyze() {
        $this->atomIs('Assignation')
             ->outIs('LEFT')
             ->atomIs(array('Variable', 'Array', 'Property', 'Staticproperty'))
             ->savePropertyAs('fullcode', 'name')
             ->inIs('LEFT')
             ->nextSibling()
             ->atomIs('Assignation')
             ->codeIs('=')
             ->outIs('LEFT')
             ->samePropertyAs('fullcode', 'name')
             ->inIs('LEFT')
             ->outIs('RIGHT')
             // No self assignation (after operation)
             ->raw('where( __.repeat( out() ).emit(hasLabel("Variable", "Array", "Property", "Staticproperty")).times('.self::MAX_LOOPING.').filter{ it.get().value("fullcode") == name}.count().is(eq(0)) )')
//             ->filter(' it.out.loop(1){true}{ it.object.atom == nameAtom}.has("fullcode", name).any() == false ')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
