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

class StrposCompare extends Analyzer {
    public function analyze() {
        $operator = $this->loadIni('php_may_return_boolean_or_zero.ini', 'functions');
        
        // if (.. == strpos(..)) {}
        $this->atomIs('Functioncall')
             ->_as('result')
             ->codeIs($operator)
             ->inIs('RIGHT')
             ->atomIs('Comparison')
             ->codeIs(array('==', '!='))
             ->outIs('LEFT')
             ->codeIs(array('0', "''", '""', 'null', 'false'))
             ->back('result');
        $this->prepareQuery();

        // if (strpos(..) == ..) {}
        $this->atomIs('Functioncall')
             ->_as('result')
             ->codeIs($operator)
             ->inIs('LEFT')
             ->atomIs('Comparison')
             ->codeIs(array('==', '!='))
             ->outIs('RIGHT')
             ->codeIs(array('0', "''", '""', 'null', 'false'))
             ->back('result');
        $this->prepareQuery();

        // if (strpos(..)) {}
        $this->atomIs('Functioncall')
             ->_as('result')
             ->codeIs($operator)
             ->inIs('CONDITION')
             ->atomIs(array('Ifthen', 'While', 'Dowhile'))
             ->back('result');
        $this->prepareQuery();

        // if ($x = strpos(..)) {}
        $this->atomIs('Functioncall')
             ->codeIs($operator)
             ->inIs('RIGHT')
             ->_as('result')
             ->atomIs('Assignation')
             ->inIs('CONDITION')
             ->atomIs(array('Ifthen', 'While', 'Dowhile'))
             ->back('result');
        $this->prepareQuery();
    }
}

?>
