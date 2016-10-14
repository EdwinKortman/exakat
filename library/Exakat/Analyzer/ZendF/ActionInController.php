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

namespace Exakat\Analyzer\ZendF;

use Exakat\Analyzer\Analyzer;

class ActionInController extends Analyzer {
    public function analyze() {
        // Methods ending with Action must be in controller
        $this->atomIs('Function')
             ->hasClass()
             // Why not Action\\$?
             ->outIs('NAME')
             ->regexIs('code', 'Action\$')
             ->goToClass()
//             ->filter('!("\\\\zend_controller_action" in it.classTree)')
             ->back('first');
        $this->prepareQuery();

        // Methods ending with Action must be public
        $this->atomIs('Function')
             ->analyzerIsNot('self')
             ->hasClass()
             // Why not Action\\$?
             ->outIs('NAME')
             ->regexIs('code', 'Action\$')
             ->hasOut(array('PRIVATE', 'PROTECTED'))
             ->goToClass()
//             ->filter('"\\\\zend_controller_action" in it.classTree')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
