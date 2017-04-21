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

class UnusedArguments extends Analyzer {
    public function dependsOn() {
        return array('Variables/Arguments',
                     'Variables/IsRead',
                     'Variables/IsModified',
                     );
    }
    
    public function analyze() {
        $isNotRead = 'where( repeat( out('.$this->linksDown.') ).emit( hasLabel("Variable").filter{ it.get().value("code") == varname; }).times('.self::MAX_LOOPING.')
                                          .where( __.in("ANALYZED").has("analyzer", "Variables/IsRead").count().is(eq(1)) )
                                          .count().is(eq(0)) )';
    
        $isNotUsed = 'where( repeat( out('.$this->linksDown.') ).emit( hasLabel("Variable").filter{ it.get().value("code") == varname; } ).times('.self::MAX_LOOPING.').count().is(eq(0)) )';

        // Arguments, not reference, function
        $this->analyzerIs('Variables/Arguments')
             ->savePropertyAs('code', 'varname')
             ->isNot('reference', true)
             ->inIs('ARGUMENT')
             ->inIs('ARGUMENTS')
             ->atomIs(self::$FUNCTION_METHOD)
             ->_as('results')
             ->hasNoClassInterfaceTrait()
             ->outIs('BLOCK')
             // this argument must be read at least once
             ->raw($isNotRead)
             ->back('results');
        $this->prepareQuery();

        // Arguments, not reference, method (class, trait)
        $this->analyzerIs('Variables/Arguments')
             ->savePropertyAs('code', 'varname')
             ->isNot('reference', true)
             ->inIs('ARGUMENT')
             ->inIs('ARGUMENTS')
             ->atomIs(self::$FUNCTION_METHOD)
             ->_as('results')

             ->hasClassTrait()
             ->hasNoOut('ABSTRACT')
             ->checkInheriting()
             ->outIs('BLOCK')
             // this argument must be read at least once
             ->raw($isNotRead)
             ->back('results');
        $this->prepareQuery();

        // Arguments, reference, function
        $this->analyzerIs('Variables/Arguments')
             ->savePropertyAs('code', 'varname')
             ->is('reference', true)
             ->inIs('ARGUMENT')
             ->inIs('ARGUMENTS')
             ->atomIs(self::$FUNCTION_METHOD)
             ->_as('results')
             ->hasNoClassInterfaceTrait()
             ->outIs('BLOCK')
             // this argument must be read or written at least once (in fact, used)
             ->raw($isNotUsed)
             ->back('results');
        $this->prepareQuery();

        // Arguments, reference, method
        $this->analyzerIs('Variables/Arguments')
             ->savePropertyAs('code', 'varname')
             ->is('reference', true)
             ->inIs('ARGUMENT')
             ->inIs('ARGUMENTS')
             ->atomIs(self::$FUNCTION_METHOD)
             ->_as('results')

             ->hasClassTrait()
             ->hasNoOut('ABSTRACT')
             ->checkInheriting()
             ->outIs('BLOCK')
             // this argument must be read or written at least once (in fact, used)
             ->raw($isNotUsed)
             ->back('results');
        $this->prepareQuery();

        // Arguments in a USE, not a reference
        $this->atomIs(self::$FUNCTION_METHOD)
             ->hasChildren('Void', 'NAME')
             ->outIs('USE')
             ->outIs('ARGUMENT')
             ->isNot('reference', true)
             ->savePropertyAs('code', 'varname')
             ->back('first')

             ->outIs('BLOCK')
             // this argument must be read or written at least once
             ->raw($isNotRead)

             ->back('first');
        $this->prepareQuery();

        // Arguments in a USE, reference
        $this->atomIs(self::$FUNCTION_METHOD)
             ->hasChildren('Void', 'NAME')
             ->outIs('USE')
             ->outIs('ARGUMENT')
             ->is('reference', true)
             ->savePropertyAs('code', 'varname')
             ->back('first')

             ->outIs('BLOCK')
             // this argument must be read or written at least once
             ->raw($isNotUsed)
             ->back('first');
        $this->prepareQuery();
    }
    
    private function checkInheriting() {
        $this->_as('method')
             ->outIs('NAME')
             ->savePropertyAs('code', 'name')
             ->goToClassTrait()
             ->raw('where( repeat( __.as("x").out("EXTENDS", "IMPLEMENTS").in("DEFINITION").where(neq("x")) ).emit().times('.self::MAX_LOOPING.')
                                     .out("BLOCK").out("ELEMENT").hasLabel("Method").out("NAME").filter{ it.get().value("code") == name}
                                     .count().is(eq(0)) 
                          )')
             ->back('method');

        return $this;
    }
}

?>
