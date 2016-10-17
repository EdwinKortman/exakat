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


namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;

class ShouldChainException extends Analyzer {
    public function analyze() {
        // omitted 3rd argument
        $this->atomIs('Catch')
             ->outIs('BLOCK')
             ->atomInside('Throw')
             ->outIs('THROW')
             ->outIs('NEW')
             ->outIs('ARGUMENTS')
             ->noChildWithRank('ARGUMENT', 2)
             ->back('first');
        $this->prepareQuery();

        // Throw again, but not the caught variable
        $this->atomIs('Catch')
             ->outIs('VARIABLE')
             ->savePropertyAs('code', 'caught')
             ->inIs('VARIABLE')
             ->outIs('BLOCK')
             ->atomInside('Throw')
             ->outIs('THROW')
             ->outIs('NEW')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->is('rank', 2)
             ->notSamePropertyAs('code', 'caught')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
