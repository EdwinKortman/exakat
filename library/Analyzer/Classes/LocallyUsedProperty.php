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


namespace Analyzer\Classes;

use Analyzer;

class LocallyUsedProperty extends Analyzer\Analyzer {
    public function dependsOn() {
        return array('Variables/StaticVariables');
    }
    
    public function analyze() {
        // normal property
        $this->atomIs('Ppp')
             ->hasNoOut('STATIC')
             ->outIs('PPP')
             ->_as('ppp')
             ->outIsIE('LEFT')
             ->savePropertyAs('propertyname', 'property')
             ->goToClass()
             ->outIs('BLOCK')
             ->atomInside('Property')
             ->outIs('PROPERTY')
             ->outIsIE('VARIABLE')
             ->samePropertyAs('code', 'property')
             ->back('ppp');
        $this->prepareQuery();

        // static property in an variable static::$c
        $this->atomIs('Ppp')
             ->hasOut('STATIC')
             ->outIs('PPP')
             ->_as('ppp')
             ->outIsIE('LEFT')
             ->savePropertyAs('code', 'property')
             ->goToClass()
             ->outIs('BLOCK')
             ->atomInside('Staticproperty')
             ->outIs('PROPERTY')
             ->outIsIE(array('VARIABLE', 'APPEND'))
             ->samePropertyAs('code', 'property')
             ->back('ppp');
        $this->prepareQuery();
    }
}

?>
