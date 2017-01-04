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


namespace Exakat\Analyzer\Constants;

use Exakat\Analyzer\Analyzer;

class UnusedConstants extends Analyzer {
    public function dependsOn() {
        return array('Constants/ConstantUsage',
                     'Structures/Truthy');
    }
    
    public function analyze() {
        $constants = $this->query('g.V().hasLabel("Analysis").has("analyzer", "Constants/ConstantUsage").out("ANALYZED").values("code").unique()');

        // Const from a define (case insensitive)
        $this->atomFunctionIs('\define')
             ->outIs('ARGUMENTS')
             ->noChildWithRank('ARGUMENT', 2) // default, case sensitive
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->noDelimiterIsNot($constants, true);
        $this->prepareQuery();

        $this->atomFunctionIs('\define')
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 2) // explicit, case sensitive
             ->analyzerIsNot('Structures/Truthy')
             ->inIs('ARGUMENT')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->noDelimiterIsNot($constants, true);
        $this->prepareQuery();

        // Const from a define (case sensitive)
        $constantsLC = array_map(function ($x) { return strtolower($x); }, $constants);
        $this->atomFunctionIs('\define')
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 2) // explicit, case sensitive
             ->analyzerIs('Structures/Truthy')
             ->inIs('ARGUMENT')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->noDelimiterIsNot($constantsLC);
        $this->prepareQuery();

        $constConstants = $this->query('g.V().hasLabel("Analysis").has("analyzer", "Constants/ConstantUsage").out("ANALYZED").values("fullnspath").unique()');
        // Const from a const
        $this->atomIs('Const')
             ->hasNoClassInterface()
             ->outIs('CONST')
             ->outIs('NAME')
             ->fullnspathIsNot($constConstants);
        $this->prepareQuery();
      }
}

?>
