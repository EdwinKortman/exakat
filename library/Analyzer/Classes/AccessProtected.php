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


namespace Analyzer\Classes;

use Analyzer;

class AccessProtected extends Analyzer\Analyzer {
    public function analyze() {
        // class::method()
        $this->atomIs('Staticmethodcall')
             ->outIs('METHOD')
             ->savePropertyAs('code', 'name')
             ->back('first')
             ->outIs('CLASS')
             ->codeIsNot(array('parent', 'static', 'self'))
             ->raw('filter{ inside = it.fullnspath; it.in.loop(1){it.object.atom != "Class"}{it.object.atom == "Class"}.has("fullnspath", inside).any() == false}')
             ->classDefinition()
             ->raw('filter{ it.out("BLOCK").out("ELEMENT").has("atom", "Function").out("NAME").has("code", name).in("NAME").out("PRIVATE").any()}')
             ->back('first');
        $this->prepareQuery();

        // parent::$property
        // static or self ::$property

        // class::$property
        $this->atomIs('Staticproperty')
             ->outIs('PROPERTY')
             ->savePropertyAs('code', 'name')
             ->back('first')
             ->outIs('CLASS')
             ->codeIsNot(array('parent', 'static', 'self'))
             ->raw('filter{ inside = it.fullnspath; it.in.loop(1){it.object.atom != "Class"}{it.object.atom == "Class"}.has("fullnspath", inside).any() == false}')
             ->classDefinition()
             ->raw('filter{ it.out("BLOCK").out("ELEMENT").has("atom", "Visibility").out("DEFINE").has("code", name).in("DEFINE").out("PRIVATE").any()}')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
