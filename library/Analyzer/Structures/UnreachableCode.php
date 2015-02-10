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


namespace Analyzer\Structures;

use Analyzer;

class UnreachableCode extends Analyzer\Analyzer {
    public function dependsOn() {
        return array('Analyzer\\Functions\\KillsApp');
    }
    
    public function analyze() {
        $this->atomIs('Return')
             ->nextSibling();
        $this->prepareQuery();

        $this->atomIs('Throw')
             ->nextSibling();
        $this->prepareQuery();

        $this->atomIs('Break')
             ->nextSibling();
        $this->prepareQuery();

        $this->atomIs('Continue')
             ->nextSibling();
        $this->prepareQuery();

        $this->atomIs('Goto')
             ->nextSibling()
             ->atomIsNot('Label');
        $this->prepareQuery();

        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR', 'T_EXIT', 'T_DIE'))
             ->fullnspath(array('\\exit', '\\die'))
             ->nextSibling();
        $this->prepareQuery();

        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
             ->functionDefinition()
             ->inIs('NAME')
             ->analyzerIs('Analyzer\\Functions\\KillsApp')
             ->back('first')
             ->nextSibling();
        $this->prepareQuery();
    }
}

?>
