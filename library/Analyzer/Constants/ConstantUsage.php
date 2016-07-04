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


namespace Analyzer\Constants;

use Analyzer;

class ConstantUsage extends Analyzer\Analyzer {
    public function analyze() {
        // Nsname that is not used somewhere else
        $this->atomIs('Nsname')
             ->hasNoIn(array('NEW', 'USE', 'NAME', 'NAMESPACE', 'EXTENDS', 'IMPLEMENTS', 'CLASS', 'CONST', 'FUNCTION', 'GROUPUSE'));
        $this->prepareQuery();

        // Identifier that is not used somewhere else
        $this->atomIs('Identifier')
             ->hasNoIn(array('NEW', 'SUBNAME', 'USE', 'NAME', 'NAMESPACE', 'CONSTANT', 'PROPERTY',
                             'CLASS', 'EXTENDS', 'IMPLEMENTS', 'CLASS', 'AS', 'VARIABLE', 'FUNCTION', 'CONST', 'GROUPUSE'))
             ->hasNoParent('String', array('INDEX', 'CONCAT'))
             ->hasNoParent('Const', array('LEFT', 'CONST'));
        $this->prepareQuery();

        // special case for Boolean and Null
        $this->atomIs(array('Boolean', 'Null'));
        $this->prepareQuery();
        
        // defined('constant') : then the string is a constant
        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
             ->fullnspathIs(array('\defined', '\constant'))
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String');
        $this->prepareQuery();
        
        // Const outside a class
    }
}

?>
