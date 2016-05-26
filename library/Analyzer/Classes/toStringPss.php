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

class toStringPss extends Analyzer\Analyzer {
    public function analyze() {
        $methods = $this->loadIni('php_magic_methods.ini', 'magicMethod');
        $methods = array_values(array_diff($methods, array('__construct', '__destruct')));
        foreach($methods as &$method) {
            $method = strtolower($method);
        }
        unset($method);
        $methodsWithoutCallStatic = array_filter($methods, function ($x) { return strtolower($x) !== '__callstatic'; });
        
        // Checking for 'static'
        $this->atomIs('Function')
             ->hasClass()
             ->outIs('NAME')
             ->code($methodsWithoutCallStatic)
             ->inIs('NAME')
             ->hasOut('STATIC')
             ->back('first');
            $this->prepareQuery();

        // Checking for 'private' and 'protected'
        $this->atomIs('Function')
             ->analyzerIsNot('self')
             ->hasClass()
             ->outIs('NAME')
             ->code($methods)
             ->inIs('NAME')
             ->hasOut(array('PRIVATE', 'PROTECTED'))
             ->back('first');
        $this->prepareQuery();

        // Checking for __callstatic (must be static and public)
        // no static
        $this->atomIs('Function')
             ->hasClass()
             ->outIs('NAME')
             ->code('__callstatic')
             ->inIs('NAME')
             ->hasNoOut('STATIC')
             ->back('first');
        $this->prepareQuery();

        // no public
        $this->atomIs('Function')
             ->analyzerIsNot('self')
             ->hasClass()
             ->outIs('NAME')
             ->code('__callstatic')
             ->inIs('NAME')
             ->hasOut(array('PROTECTED', 'PRIVATE'))
             ->back('first');
        $this->prepareQuery();
    }
}

?>
