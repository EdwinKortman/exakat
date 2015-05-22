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


namespace Tokenizer;

class _Global extends TokenAuto {
    static public $operators = array('T_GLOBAL');
    static public $atom = 'Global';

    public function _check() {
    // global $x; (nothing more)
        $this->conditions = array( 0 => array('token' => _Global::$operators),
                                   1 => array('atom'  => array('Variable', 'String', 'Staticconstant', 'Static', 'Property', 'Array' )),
                                   2 => array('token' => 'T_SEMICOLON')
                                 );
        
        $this->actions = array('transform'    => array( 1 => 'GLOBAL'),
                               'atom'         => 'Global',
                               'cleanIndex'   => true,
                               'addSemicolon' => 'it'
                               );
        $this->checkAuto();

    // global $x, $y, $z;
        $this->conditions = array( 0 => array('token'     => _Global::$operators),
                                   1 => array('atom'      => 'Arguments'),
                                   2 => array('filterOut' => 'T_COMMA'),
                                 );
        
        $this->actions = array('toGlobal'     => true,
                               'atom'         => 'Global',
                               'addSemicolon' => 'it');
        $this->checkAuto();

        return false;
    }

    public function fullcode() {
        return <<<GREMLIN

if (fullcode.out('GLOBAL').count() == 1) {
    fullcode.setProperty('fullcode', "global " + fullcode.out("GLOBAL").next().getProperty('fullcode'));
} else {
    s = [];
    fullcode.out("GLOBAL").sort{it.rank}._().each{ s.add(it.fullcode); };

    fullcode.setProperty('fullcode', "global " + s.join(', '));
}

GREMLIN;
    }

}
?>
