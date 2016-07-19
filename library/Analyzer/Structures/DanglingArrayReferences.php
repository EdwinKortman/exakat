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

class DanglingArrayReferences extends Analyzer\Analyzer {
    public function analyze() {
        // foreach($a as &$v) {}. (unset($v));
        // foreach($a as $k => &$v) {}. (unset($v));
        $this->atomIs('Foreach')
             ->outIsIE('VALUE')
             ->is('reference', true)
             ->savePropertyAs('code', 'array')
             ->back('first')
             ->nextSibling()

            // is it unset($x); ?
             ->raw('where( __.hasLabel("Functioncall").has("fullnspath", "\\\\unset").out("ARGUMENTS").out("ARGUMENT").filter{ it.get().value("code") == array }.count().is(eq(0)) )')

            // is is (unset) $x;? 
             ->raw('where( __.hasLabel("Cast").has("token", "T_UNSET_CAST").out("CAST").filter{ it.get().value("code") == array }.count().is(eq(0)) )')

             ->back('first');
        $this->prepareQuery();
    }
}

?>
