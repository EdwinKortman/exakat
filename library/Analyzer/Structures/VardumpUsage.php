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

class VardumpUsage extends Analyzer\Analyzer {
    public function analyze() {
        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
             ->fullnspath(array('\\var_dump', '\\print_r'))
             ->outIs('ARGUMENTS')
             ->rankIs('ARGUMENT', 1)
             ->codeIsNot('true')
             ->back('first');
        $this->prepareQuery();

        // var_dump($x, 1) will not print directly, so it's OK 
        // (well, we need to check if the result string is not printed now...)
        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
             ->fullnspath(array('\\var_dump', '\\print_r'))
             ->outIs('ARGUMENTS')
             ->noChildWithRank('ARGUMENT', 1)
             ->back('first');
        $this->prepareQuery();
        
//         call_user_func_array('var_dump', )
        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
             ->fullnspath(array('\\call_user_func_array', '\\call_user_func'))
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->is('rank', 0)
             ->atomIs('String')
             ->tokenIsNot('T_QUOTE')
             ->noDelimiter(array('print_r', 'var_dump'))
             ->back('first');
        $this->prepareQuery();
    }
}

?>
