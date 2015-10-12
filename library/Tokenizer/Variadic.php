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

class Variadic extends TokenAuto {
    static public $operators = array('T_ELLIPSIS');
    static public $atom = 'Variadic (unused)';

    protected $phpVersion = '5.6+';

    public function _check() {
        // function x(...$a) {} or functioncall  x(...$a);
        $this->conditions = array( 0 => array('token'    => Variadic::$operators,
                                              'atom'     => 'none'),
                                   1 => array('atom'     => array('Variable', 'Property', 'Staticproperty', 'Staticmethodcall',
                                                                  'Staticconstant','Magicconstant', 'Integer', 'Array', 'Methodcall',
                                                                  'Identifier', 'Nsname', 'Boolean', 'Null', 'Functioncall')),
                                   2 => array('notToken' => array('T_OBJECT_OPERATOR', 'T_DOUBLE_COLON'))
        );
        
        $this->actions = array('transform'    => array( 0         => 'DROP'),
                               'propertyNext' => array('variadic' => true),
                               'fullcode'     => true);
        $this->checkAuto();

        return false;
    }

    public function fullcode() {
        return <<<GREMLIN

fullcode.setProperty('fullcode', "..." + fullcode.getProperty('fullcode'));

GREMLIN;
    }
}

?>
