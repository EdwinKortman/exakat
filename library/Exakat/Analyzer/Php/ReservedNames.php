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


namespace Exakat\Analyzer\Php;

use Exakat\Analyzer\Analyzer;

class ReservedNames extends Analyzer {

    public function analyze() {
        $reservedNames = $this->loadIni('php_keywords.ini', 'keyword');

        // functions/methods names
        $this->atomIs('Function')
             ->outIs('NAME')
             ->codeIs($reservedNames)
             ->back('first');
        $this->prepareQuery();

        // classes
        $this->atomIs('Class')
             ->outIs('NAME')
             ->codeIs($reservedNames)
             ->back('first');
        $this->prepareQuery();

        // trait
        $this->atomIs('Trait')
             ->outIs('NAME')
             ->codeIs($reservedNames)
             ->back('first');
        $this->prepareQuery();

        // interface
        $this->atomIs('Interface')
             ->outIs('NAME')
             ->codeIs($reservedNames)
             ->back('first');
        $this->prepareQuery();

        // methodcall
        $this->atomIs('Methodcall')
             ->outIs('METHOD')
             ->codeIs($reservedNames)
             ->back('first');
        $this->prepareQuery();

        // property
        $this->atomIs('Property')
             ->outIs('METHOD')
             ->codeIs($reservedNames)
             ->back('first');
        $this->prepareQuery();

        // variables
        foreach($reservedNames as &$variable) {
            $variable = '$'.$variable;
        }
        unset($variable);
        $this->atomIs('Variable')
             ->codeIs($reservedNames);
        $this->prepareQuery();
    }
}

?>
