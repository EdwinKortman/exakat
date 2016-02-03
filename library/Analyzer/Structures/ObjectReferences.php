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


namespace Analyzer\Structures;

use Analyzer;

class ObjectReferences extends Analyzer\Analyzer {
    public function analyze() {

        // f(stdclass &$x)
        $this->atomIs('Function')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->atomIs('Typehint')
             ->outIs('CLASS')
             ->codeIsNot(array('callable', 'array', 'string', 'int', 'float', 'bool'))
             ->inIs('CLASS')
             ->outIs('VARIABLE')
             ->atomIs('Variable')
             ->is('reference', true);
        $this->prepareQuery();

        // f(stdclass &$x = null)
        $this->atomIs('Function')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->atomIs('Typehint')
             ->outIs('CLASS')
             ->codeIsNot(array('callable', 'array', 'string', 'int', 'float', 'bool'))
             ->inIs('CLASS')
             ->outIs('VARIABLE')
             ->atomIs('Assignation')
             ->outIs('LEFT')
             ->atomIs('Variable')
             ->is('reference', true);
        $this->prepareQuery();

        // f(&$x) and $x->y();
        $this->atomIs('Function')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->atomIs('Variable')
             ->is('reference', true)
             ->savePropertyAs('code', 'variable')
             ->inIs('ARGUMENT')
             ->inIs('ARGUMENTS')
             ->outIs('BLOCK')
             ->atomInside('Methodcall')
             ->outIs('OBJECT')
             ->samePropertyAs('code', 'variable');
        $this->prepareQuery();

        // f(&$x) and $x->y;
        $this->atomIs('Function')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->atomIs('Variable')
             ->is('reference', true)
             ->savePropertyAs('code', 'variable')
             ->inIs('ARGUMENT')
             ->inIs('ARGUMENTS')
             ->outIs('BLOCK')
             ->atomInside('Property')
             ->outIs('OBJECT')
             ->samePropertyAs('code', 'variable');
        $this->prepareQuery();
        
        // foreach($a as &$b) { $b->method;}
        $this->atomIs('Foreach')
             ->outIs('VALUE')
             ->is('reference', true)
             ->savePropertyAs('code', 'variable')
             ->back('first')
             ->outIs('BLOCK')
             ->atomInside('Methodcall')
             ->outIs('OBJECT')
             ->samePropertyAs('code', 'variable');
        $this->prepareQuery();

        // foreach($a as &$b) { $b->property;}
        $this->atomIs('Foreach')
             ->outIs('VALUE')
             ->is('reference', true)
             ->savePropertyAs('code', 'variable')
             ->back('first')
             ->outIs('BLOCK')
             ->atomInside('Property')
             ->outIs('OBJECT')
             ->samePropertyAs('code', 'variable');
        $this->prepareQuery();
        
        // todo $x = new object; then &$x;
    }
}

?>
