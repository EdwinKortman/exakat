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


namespace Exakat\Analyzer\Classes;

use Exakat\Analyzer\Analyzer;

class StaticMethodsCalledFromObject extends Analyzer {
    public function dependsOn() {
        return array('Classes/StaticMethods',
                    );
    }

    public function analyze() {
        $query = <<<GREMLIN
g.V().hasLabel("Method")
     .where( __.in("METHOD").hasLabel("Class", "Trait") )
     .where( __.out("STATIC") )
     .out("NAME")
     .values("code")
     .unique()
GREMLIN;
        $methods = $this->query($query)->toArray();
        if (empty($methods)) {
            return;
        }

        // $a->staticMethod (Anywhere in the code)
        $this->atomIs('Methodcall')
             ->outIs('OBJECT')
             ->codeIsNot('$this')
             ->back('first')
             ->outIs('METHOD')
             ->codeIs($methods, true)
             ->back('first');
        $this->prepareQuery();

        // $this->staticMethod (In the local class tree)
        $this->atomIs('Methodcall')
             ->outIs('OBJECT')
             ->codeIs('$this')
             ->back('first')
             ->outIs('METHOD')
             ->outIs('NAME')
             ->savePropertyAs('code', 'name')
             ->goToClass()
             ->goToAllParents(self::INCLUDE_SELF)
             ->outIs('METHOD')
             ->hasOut('STATIC')
             ->outIs('NAME')
             ->samePropertyAs('code', 'name')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
