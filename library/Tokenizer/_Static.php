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

class _Static extends TokenAuto {
    static public $operators = array('T_STATIC');
    static public $atom = 'Static';

    public function _check() {
        $values = array('T_EQUAL', 'T_COMMA');

    // class x { static function f() }
        $this->conditions = array( 0 => array('token' => _Static::$operators),
                                   1 => array('token' => 'T_FUNCTION'),
                                 );
        $this->actions = array('toOption' => 1,
                               'atom'     => 'Static');
        $this->checkAuto();

    // class x { static public function x() }
        $this->conditions = array( 0 => array('token' => _Static::$operators),
                                   1 => array('token' => array('T_PRIVATE', 'T_PUBLIC', 'T_PROTECTED')),
                                   2 => array('token' => array('T_FUNCTION')),
                                 );
        $this->actions = array('toOption' => 2,
                               'atom'     => 'Static');
        $this->checkAuto();

    // class x { static $x }
        $this->conditions = array(-1 => array('notToken'  => _Ppp::$operators),
                                   0 => array('token'     => _Static::$operators),
                                   1 => array('atom'      => array('Variable', 'String', 'Staticconstant', )),
                                   2 => array('filterOut' => $values)
                                 );
        
        $this->actions = array('to_ppp'       => 1,
                               'atom'         => 'Static',
                               'cleanIndex'   => true,
                               'addSemicolon' => 'x'
                               );
        $this->checkAuto();

    // class x { static private $s }
        $this->conditions = array( 0 => array('token' => _Static::$operators),
                                   1 => array('token' => array('T_PRIVATE', 'T_PUBLIC', 'T_PROTECTED')),
                                   2 => array('token' => 'T_VARIABLE'),
                                 );
        $this->actions = array('toOption' => 1,
                               'atom'     => 'Static');
        $this->checkAuto();


    // class x { static $x = 2 }
        $this->conditions = array(-1 => array('notToken'  => _Ppp::$operators),
                                   0 => array('token'     => _Static::$operators),
                                   1 => array('atom'      => 'Assignation'),
                                   2 => array('filterOut' => $values)
                                 );
        
        $this->actions = array('to_ppp_assignation' => 1,
                               'atom'               => 'Static',
                               'addSemicolon'       => 'x' );
        $this->checkAuto();

    // class x { static public $x = 2 }

        $this->conditions = array( 0 => array('token' => _Static::$operators),
                                   1 => array('token' => array('T_PRIVATE', 'T_PUBLIC', 'T_PROTECTED')),
                                   2 => array('atom'  => 'Assignation'),
                                   3 => array('filterOut' => $values)
                                 );
        
        $this->actions = array('toOption' => 1,
                               'atom'     => 'Static');
        $this->checkAuto();


    // class x { static $x, $y }
        $this->conditions = array(-1 => array('token'     => array('T_PROTECTED', 'T_PRIVATE', 'T_PUBLIC')),
                                   0 => array('token'     => _Static::$operators),
                                   1 => array('atom'      => 'Arguments'),
                                   2 => array('filterOut' => 'T_COMMA'),
                                 );
        
        $this->actions = array('toVarNew' => 'Atom',
                               'atom'     => 'Static');
        $this->checkAuto();

    // class x { static private $x, $y }
        $this->conditions = array( 0 => array('token' => _Static::$operators),
                                   1 => array('token' => array('T_PROTECTED', 'T_PRIVATE', 'T_PUBLIC')),
                                   2 => array('atom'  => 'Arguments'),
                                   3 => array('filterOut'  => 'T_COMMA'),
                                 );
        
        $this->actions = array('toOption' => 1,
                               'atom'     => 'Static');
        $this->checkAuto();


    // class x { static function f() }
        $this->conditions = array( 0 => array('token' => _Static::$operators),
                                   1 => array('token' => array('T_FUNCTION')),
                                 );
        $this->actions = array('toOption' => 1,
                               'atom'     => 'Static');
        $this->checkAuto();

    // class x { static private function f() }
        $this->conditions = array( 0 => array('token' => _Static::$operators),
                                   1 => array('token' => array('T_PRIVATE', 'T_PUBLIC', 'T_PROTECTED', 'T_FINAL', 'T_ABSTRACT')),
                                   2 => array('token' => 'T_FUNCTION'),
                                 );
        $this->actions = array('toOption' => 2,
                               'atom'     => 'Static');
        $this->checkAuto();

    // class x { static private final function f() }
        $this->conditions = array( 0 => array('token' => _Static::$operators),
                                   1 => array('token' => array('T_PRIVATE', 'T_PUBLIC', 'T_PROTECTED', 'T_FINAL', 'T_ABSTRACT')),
                                   2 => array('token' => array('T_PRIVATE', 'T_PUBLIC', 'T_PROTECTED', 'T_FINAL', 'T_ABSTRACT')),
                                   3 => array('token' => 'T_FUNCTION'),
                                 );
        $this->actions = array('toOption' => 3,
                               'atom'     => 'Static');
        $this->checkAuto();

    // class x { static $x, $y }
        $this->conditions = array(-1 => array('notToken'  => array('T_NEW', 'T_PROTECTED', 'T_PRIVATE', 'T_PUBLIC')),
                                   0 => array('token'     => _Static::$operators),
                                   1 => array('atom'      => 'Arguments'),
                                   2 => array('filterOut' => 'T_COMMA'),
                                 );
        
        $this->actions = array('toVarNew' => 'Static',
                               'atom'     => 'Static',
                               );
        $this->checkAuto();



    // static :: ....
        $this->conditions = array( 0 => array('token' => _Static::$operators),
                                   1 => array('token' => 'T_DOUBLE_COLON'),
                                 );

        $this->actions = array('atom'     => 'Static');
        $this->checkAuto();

    // static :: ....
        $this->conditions = array( -1 => array('token' => 'T_INSTANCEOF'),
                                    0 => array('token' => _Static::$operators),
                                 );
        $this->actions = array('atom'     => 'Static');
        $this->checkAuto();

        return false;
    }

    public function fullcode() {
        $token = new _Function(Token::$client);
        return $token->fullcode();
    }
}
?>
