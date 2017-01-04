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


namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;

class pregOptionE extends Analyzer {
    public function analyze() {
        $functions = array('\preg_replace', '\mb_eregi');
        // delimiters
        $delimiters = '=~/|`%#\\$\\*!,@\\\\{\\\\(\\\\[';
        
        $makeDelimiters = ' sideEffect{ 
    if (delimiter == "{") { delimiter = "\\\\{"; delimiterFinal = "\\\\}"; } 
    else if (delimiter == "(") { delimiter = "\\\\("; delimiterFinal = "\\\\)"; } 
    else if (delimiter == "[") { delimiter = "\\\\["; delimiterFinal = "\\\\]"; } 
    else if (delimiter == "*") { delimiter = "\\\\*"; delimiterFinal = "\\\\*"; } 
    else { delimiterFinal = delimiter; } 
}';

        // preg_match with a string
        $this->atomFunctionIs($functions)
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 0)
             ->tokenIs('T_CONSTANT_ENCAPSED_STRING')
             ->raw(' sideEffect{ delimiter = it.get().value("noDelimiter")[0]; }')
             ->raw($makeDelimiters)
             ->regexIs('noDelimiter', '^(" + delimiter + ").*(" + delimiterFinal + ")(.*e.*)\\$')
             ->back('first');
        $this->prepareQuery();

        // With an interpolated string "a $x b"
        $this->atomFunctionIs($functions)
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->outWithRank('CONCAT', 0)
             ->raw('sideEffect{delimiter = it.get().value("noDelimiter")[0]; }')
             ->inIs('CONCAT')
             ->raw($makeDelimiters)
             ->regexIs('fullcode', '^.(" + delimiter + ").*(" + delimiterFinal + ")(.*e.*).\\$')
             ->back('first');
        $this->prepareQuery();

        // with a concatenation
        $this->atomFunctionIs($functions)
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('Concatenation')
             ->outWithRank('CONCAT', 0)
             ->raw('sideEffect{delimiter = it.get().value("noDelimiter")[0]; }')
             ->inIs('CONCAT')
             ->raw($makeDelimiters)
             ->regexIs('fullcode', '^.(" + delimiter + ").*(" + delimiterFinal + ")(.*e.*).\\$')
             ->back('first');
        $this->prepareQuery();
// Actual letters used for Options in PHP imsxeuADSUXJ (others may yield an error) case is important
    }
}

?>
