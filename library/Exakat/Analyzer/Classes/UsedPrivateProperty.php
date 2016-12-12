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


namespace Exakat\Analyzer\Classes;

use Exakat\Analyzer\Analyzer;

class UsedPrivateProperty extends Analyzer {

    public function analyze() {
        // property used in a staticproperty \a\b::$b
        $this->atomIs('Ppp')
             ->hasOut('PRIVATE')

             ->outIs('PPP')
             ->_as('ppp')
             ->outIsIE('LEFT')
             ->savePropertyAs('code', 'property')
             ->goToClassTrait()
             ->hasName()
             ->savePropertyAs('fullnspath', 'classe')
             ->outIs('BLOCK')
             ->raw('where( __.repeat( __.out() ).emit( hasLabel("Staticproperty") ).times('.self::MAX_LOOPING.')
                                                           .out("CLASS").filter{ it.get().value("token") in ["T_STRING", "T_NS_SEPARATOR", "T_STATIC" ] }.filter{ it.get().value("fullnspath") == classe }.in("CLASS")
                                                           .out("PROPERTY").filter{ it.get().value("code") == property }
                                                           .count().is(neq(0)) )')
             ->back('ppp')
             ->analyzerIsNot('self');
        $this->prepareQuery();

        // property used in a static property static::$b[] or self::$b[]
        $this->atomIs(array('Class', 'Trait'))
             ->hasName()
             ->savePropertyAs('fullnspath', 'fnp')
             ->outIs('BLOCK')
             ->outIs('ELEMENT')
             ->atomIs('Ppp')
             ->analyzerIsNot('self')
             ->hasOut('PRIVATE')
             ->outIs('PPP')
             ->outIsIE('LEFT')
             ->_as('ppp')
             ->savePropertyAs('code', 'x')
             ->inIsIE('LEFT')
             ->inIs('PPP')
             ->inIs('ELEMENT')
             ->atomInside('Staticproperty')
             ->outIs('CLASS')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR', 'T_STATIC'))
             ->fullnspathIs('fnp')
             ->inIs('CLASS')
             ->outIs('PROPERTY')
             ->atomIs('Array')
             ->outIs('VARIABLE')
             ->samePropertyAs('code', 'x')
             ->back('ppp')
             ->analyzerIsNot('self');
        $this->prepareQuery();

        // property used in a normal methodcall with $this $this->b()
        $this->atomIs(array('Class', 'Trait'))
             ->hasName()
             ->savePropertyAs('fullnspath', 'classname')
             ->outIs('BLOCK')
             ->outIs('ELEMENT')
             ->atomIs('Ppp')
             ->hasOut('PRIVATE')
             ->analyzerIsNot('self')
             ->outIs('PPP')
             ->savePropertyAs('propertyname', 'x')
             ->_as('ppp')
             ->inIs('PPP')
             ->inIs('ELEMENT')
             ->atomInside('Property')
             ->outIs('OBJECT')
             ->codeIs('$this')
             ->inIs('OBJECT')
             ->outIs('PROPERTY')
             ->samePropertyAs('code', 'x')
             ->back('ppp')
             ->analyzerIsNot('self');
        $this->prepareQuery();
    }
}
?>
