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
namespace Exakat\Analyzer\Php;

use Exakat\Analyzer\Analyzer;

class SetExceptionHandlerPHP7 extends Analyzer {
    public function dependsOn() {
        return array('Functions/MarkCallable');
    }
    
    public function analyze() {
        // With function name in a string
        $this->atomFunctionIs('\set_exception_handler')
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->hasNoOut('CONCAT')
             ->regexIsNot('noDelimiter', '::')
             ->analyzerIs('Functions/MarkCallable')
             ->functionDefinition()
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->outIs('TYPEHINT')
             ->fullnspathIsNot('\\throwable')
             ->back('first');
        $this->prepareQuery();

        // With class::method name in a string
        $this->atomFunctionIs('\set_exception_handler')
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->hasNoOut('CONCAT')
             ->regexIs('noDelimiter', '::')
             ->raw('sideEffect{ methode = it.get().value("cbMethod") }')
             ->analyzerIs('Functions/MarkCallable')
             ->classDefinition()
             ->outIs('BLOCK')
             ->outIs('ELEMENT')
             ->atomIs('Function')
             ->outIs('NAME')
             ->samePropertyAs('code', 'methode')
             ->inIs('NAME')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->outIs('TYPEHINT')
             ->fullnspathIsNot('\\throwable')
             ->back('first');
        $this->prepareQuery();

        // With parent:method name in a string

        // With closure
        $this->atomFunctionIs('\set_exception_handler')
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('Function')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->outIs('TYPEHINT')
             ->fullnspathIsNot('\\throwable')
             ->back('first');
        $this->prepareQuery();

        // With array (class + method)
        $this->atomFunctionIs('\set_exception_handler')
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 0)
             ->functioncallIs('\\array')
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 1)
             ->atomIs('String')
             ->hasNoOut('CONCAT')
             ->raw('sideEffect{ methode = it.get().value("noDelimiter") }')
             ->inIs('ARGUMENT')
             ->outWithRank('ARGUMENT', 0)
             ->classDefinition()
             ->outIs('BLOCK')
             ->outIs('ELEMENT')
             ->atomIs('Function')
             ->outIs('NAME')
             ->samePropertyAs('code', 'methode')
             ->inIs('NAME')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->outIs('TYPEHINT')
             ->fullnspathIsNot('\\throwable')
             ->back('first');
        $this->prepareQuery();

        // With array (object + method)
    }
}

?>
