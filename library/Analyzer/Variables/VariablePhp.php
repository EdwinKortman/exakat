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


namespace Analyzer\Variables;

use Analyzer;

class VariablePhp extends Analyzer\Analyzer {
    public static $variables = array('$_GET','$_POST','$_COOKIE','$_FILES','$_SESSION',
                                     '$_REQUEST','$_ENV', '$_SERVER',
                                     '$PHP_SELF','$HTTP_RAW_POST_DATA',
                                     '$HTTP_GET_VARS','$HTTP_POST_VARS', '$HTTP_POST_FILES', '$HTTP_ENV_VARS', '$HTTP_SERVER_VARS', '$HTTP_COOKIE_VARS',
                                     '$GLOBALS', '$this',
                                     '$argv', '$argc');

    public function dependsOn() {
        return array('Variables/Variablenames');
    }
    
    public function analyze() {
        $this->atomIs('Variable')
             ->analyzerIs('Variables/Variablenames')
             ->codeIs(VariablePhp::$variables, true);
    }
}

?>
