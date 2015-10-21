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

class AlteringForeachWithoutReference extends Analyzer\Analyzer {
    public function dependsOn() {
        return array('Variables/IsModified');
    }
    
    public function analyze() {
        $this->atomIs('Foreach')
             ->outIs('SOURCE')
             ->atomIs('Variable')
             ->savePropertyAs('code', 'source')
             ->inIs('SOURCE')

             ->outIs('VALUE')
             ->atomIs('Keyvalue')
             ->outIs('KEY')
             ->savePropertyAs('code', 'key')
             ->inIs('KEY')
             ->inIs('VALUE')

             ->outIs('BLOCK')
             ->atomInside('Array')
             ->filter('it.in("CAST").has("token", "T_UNSET_CAST").any() == false')
             ->filter('it.in("ARGUMENT").in("ARGUMENTS").has("token", "T_UNSET").any() == false')
             ->outIs('VARIABLE')
             ->analyzerIs('Variables/IsModified')
             ->samePropertyAs('code', 'source')
             ->inIs('VARIABLE')

             ->outIs('INDEX')
             ->samePropertyAs('code', 'key')

             ->back('first');
        $this->prepareQuery();
    }
}

?>
