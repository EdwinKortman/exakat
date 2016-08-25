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


namespace Analyzer\Composer;

use Analyzer;

class IsComposerClass extends Analyzer\Analyzer {
    public function analyze() {
        $data = new \Data\Composer();

        $classes = $data->getComposerClasses();
        $classesFullNP = $this->makeFullNsPath($classes);
        
        $this->atomIs('Class')
             ->outIs(array('IMPLEMENTS', 'EXTENDS'))
             ->fullnspathIs($classesFullNP);
        $this->prepareQuery();

        $this->atomIs('Instanceof')
             ->outIs('CLASS')
             ->tokenIs(array('T_NS_SEPARATOR', 'T_STRING'))
             ->atomIsNot('Array')
             ->fullnspathIs($classesFullNP);
        $this->prepareQuery();

        $this->atomIs('Function')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->outIs('TYPEHINT')
             ->fullnspathIs($classesFullNP);
        $this->prepareQuery();

        $this->atomIs('New')
             ->outIs('NEW')
             ->tokenIs(array('T_NS_SEPARATOR', 'T_STRING'))
             ->atomIsNot('Array')
             ->fullnspathIs($classesFullNP);
        $this->prepareQuery();
    }
}

?>
