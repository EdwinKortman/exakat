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

namespace Exakat\Analyzer\Variables;

use Exakat\Analyzer\Analyzer;

class Globals extends Analyzer {
    public function analyze() {
        // Global in a function
        $this->atomIs('Globaldefinition')
             ->savePropertyAs('code', 'name')
             ->goToFunction(array('Function', 'Method', 'Closure'))
             ->outIs('BLOCK')
             ->atomInside(self::$VARIABLES_ALL)
             ->samePropertyAs('code', 'name');
        $this->prepareQuery();

        // Global in a function
        $this->atomIs('Variablearray')
             ->codeIs('$GLOBALS')
             ->inIs('VARIABLE');
        $this->prepareQuery();

        // implicit global 
        $this->atomIs(self::$VARIABLES_ALL)
             ->codeIsNot(array('$GLOBALS', '$_POST'))
             ->hasNoClassInterfaceTrait()
             ->hasNoFunction(array('Function', 'Method', 'Closure'));
        $this->prepareQuery();
    }
}

?>
