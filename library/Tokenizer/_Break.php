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

class _Break extends TokenAuto {
    static public $operators = array('T_BREAK');
    static public $atom = 'Break';
    
    public function _check() {
        $this->conditions = array(0 => array('token'     => _Break::$operators,
                                             'atom'      => 'none'),
                                  1 => array('atom'      => 'yes'),
                                  2 => array('filterOut' => array_merge(Addition::$operators, Multiplication::$operators)),
                                  );
        
        $this->actions = array('transform'    => array( 1 => 'LEVEL'),
                               'atom'         => 'Break',
                               'addSemicolon' => 'it',
                               'cleanIndex'   => true);
        $this->checkAuto();

        return false;
    }

    public function fullcode() {
        return <<<GREMLIN

fullcode.setProperty('fullcode',  "break " + fullcode.out("LEVEL").next().getProperty('fullcode'));

GREMLIN;
    }

}

?>
