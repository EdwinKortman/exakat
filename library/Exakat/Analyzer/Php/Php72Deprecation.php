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

class Php72Deprecation extends Analyzer {
    protected $phpVersion = '7.2-';
    
    public function dependsOn() {
        return array('Variables/Variablenames');
    }
    
    public function analyze() {
        // Definition of \\__autoload
        $this->atomIs('Function')
             ->hasNoClassTrait()
             ->hasName()
             ->fullnspathIs('\\__autoload')
             ->back('first');
        $this->prepareQuery();

        // Usage of \\create_function
        $this->atomFunctionIs(array('\\create_function', '\\gmp_random', '\\each'));
        $this->prepareQuery();
        
        // Usage of \\parse_str with no 2nd argument
        $this->atomFunctionIs('\\parse_str')
             ->outIs('ARGUMENTS')
             ->noChildWithRank('ARGUMENT', 1)
             ->back('first');
        $this->prepareQuery();

        // Usage of \\assert with string argument
        $this->atomFunctionIs('\\assert')
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs(array('String', 'Concatenation', 'Heredoc'))
             ->back('first');
        $this->prepareQuery();
        
        // usage of $php_errormsg
        $this->atomIs(self::$VARIABLES_ALL)
             ->analyzerIs('Variables/Variablenames')
             ->codeIs('$php_errormsg', self::CASE_SENSITIVE);
        $this->prepareQuery();

        // usage of (unset)
        $this->atomIs('Cast')
             ->tokenIs('T_UNSET_CAST');
        $this->prepareQuery();
        
        //mbstring.func_overload
        // error handler 's 5th argument
    }
}

?>
