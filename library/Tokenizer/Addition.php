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


namespace Tokenizer;

class Addition extends TokenAuto {
    static public $operators = array('T_PLUS','T_MINUS');
    static public $atom = 'Addition';
    
    public function _check() {
        // note : Multiplication:: and Addition:: operators are the same!
        $this->conditions = array(-2 => array('filterOut' => array_merge(Property::$operators,       Staticproperty::$operators,
                                                                         Concatenation::$operators,  Sign::$operators,
                                                                         Multiplication::$operators, Power::$operators)),
                                  -1 => array('atom'      => Multiplication::$operands ),
                                   0 => array('token'     => Addition::$operators,
                                              'atom'      => 'none'),
                                   1 => array('atom'      => Multiplication::$operands),
                                   2 => array('filterOut' => array_merge(array('T_OPEN_PARENTHESIS', 'T_OPEN_CURLY', 'T_OPEN_BRACKET'),
                                                                         Property::$operators,      Staticproperty::$operators,
                                                                         Multiplication::$operators, Power::$operators,
                                                                         Assignation::$operators)
                                   ),
        );
        
        $this->actions = array('transform'    => array( 1 => 'RIGHT',
                                                       -1 => 'LEFT'),
                               'atom'         => 'Addition',
                               'cleanIndex'   => true,
                               'addSemicolon' => 'it');
        $this->checkAuto();
        
        return false;
    }

    public function fullcode() {
        return <<<GREMLIN

fullcode.setProperty('fullcode', fullcode.out("LEFT").next().getProperty('fullcode') + " " + fullcode.getProperty('code') + " " +
                                 fullcode.out("RIGHT").next().getProperty('fullcode'));

GREMLIN;
    }

}
?>
