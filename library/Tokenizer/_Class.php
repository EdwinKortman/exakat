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

class _Class extends TokenAuto {
    static public $operators = array('T_CLASS');
    static public $atom = 'Class';

    public function _check() {
    // class ( arguments ) {} Get the arguments
        $this->conditions = array( 0 => array('token' => _Class::$operators),
                                   1 => array('token' => 'T_OPEN_PARENTHESIS'),
                                   2 => array('atom'  => 'Arguments'),
                                   3 => array('token' => 'T_CLOSE_PARENTHESIS')
                                 );
        
        $this->actions = array('transform'   => array(   1 => 'DROP', 
                                                         2 => 'ARGUMENTS',
                                                         3 => 'DROP',
                                                         ),
                               'keepIndexed' => true,
                               'atom'        => 'Class',
                               'cleanIndex'  => true
                               );
        $this->checkAuto();

    // class x {} Get the name
        $this->conditions = array( 0 => array('token' => _Class::$operators),
                                   1 => array('atom'  => array('Identifier', 'Null', 'Boolean'))
                                 );
        
        $this->actions = array('transform'   => array(   1 => 'NAME'),
                               'keepIndexed' => true,
                               'atom'        => 'Class',
                               'cleanIndex'  => true);
        $this->checkAuto();

    // class x extends y {} get the extends
        $this->conditions = array( 0 => array('token'    => _Class::$operators),
                                   1 => array('token'    => 'T_EXTENDS'),
                                   2 => array('atom'     => array('Identifier', 'Nsname')),
                                   3 => array('notToken' => 'T_NS_SEPARATOR'),
                                 );
        
        $this->actions = array('transform'   => array( 1 => 'DROP',
                                                       2 => 'EXTENDS'),
                               'keepIndexed' => true,
                               'cleanIndex'  => true
                               );
        $this->checkAuto();

    // class x implements a {} get the implements
        $this->conditions = array( 0 => array('token'     => _Class::$operators),
                                   1 => array('token'     => 'T_IMPLEMENTS'),
                                   2 => array('atom'      => array('Identifier', 'Nsname', 'Arguments')),
                                   3 => array('filterOut' => array('T_COMMA', 'T_NS_SEPARATOR'))
                                 );
        
        $this->actions = array('transform'     => array( 1 => 'DROP',
                                                         2 => 'IMPLEMENTS'),
                               'property'      => array('rank' => 0),
                               'arg2implement' => true,
                               'keepIndexed'   => true,
                               'cleanIndex'    => true
                               );
        $this->checkAuto();

    // class x { // some real code} get the block
        $this->conditions = array( 0 => array('token'    => _Class::$operators),
                                   1 => array('token'    => 'T_OPEN_CURLY',
                                              'property' => array('association' => 'Class')),
                                   2 => array('atom'     => array('Sequence', 'Void')),
                                   3 => array('token'    => 'T_CLOSE_CURLY')
                                  );
        
        $this->actions = array('transform'    => array(1 => 'DROP',
                                                       2 => 'BLOCK',
                                                       3 => 'DROP'),
                               'atom'         => 'Class',
                               'addAlwaysSemicolon' => 'it',
                               'makeBlock'    => 'BLOCK',
                               'cleanIndex'   => true
                               );
        $this->checkAuto();

        return false;
    }

    public function fullcode() {
        return <<<GREMLIN
fullcode.fullcode = "class";

if (fullcode.out('NAME').any() == false && fullcode.out('ARGUMENTS').any() == false) {
    arguments = g.addVertex(null, [code:'', fullcode:'', atom:'Void', token:'T_VOID',virtual:true, line:it.line]);
    g.addEdge(fullcode, arguments, 'ARGUMENTS');
}

// class name
fullcode.out("NAME").each{ fullcode.fullcode = fullcode.fullcode + ' ' + it.code;}

// class arguments
fullcode.out("ARGUMENTS").each{ 
    if (it.token != 'T_VOID') {
        fullcode.fullcode = fullcode.fullcode + ' (' + it.fullcode + ')';
    }
}

// abstract
fullcode.out("ABSTRACT").each{ fullcode.fullcode = 'abstract ' + fullcode.fullcode;}

// final
fullcode.out("FINAL").each{ fullcode.fullcode = 'final ' + fullcode.fullcode;}

// extends
fullcode.out("EXTENDS").each{ fullcode.fullcode = fullcode.fullcode + " extends " + it.fullcode;}

// implements
if (fullcode.out("IMPLEMENTS").count() > 0) {
    s = [];
    fullcode.out("IMPLEMENTS").sort{it.rank}._().each{ s.add(it.fullcode); };
    fullcode.fullcode = fullcode.fullcode + " implements " + s.join(", ");
}

GREMLIN;
    }

}
?>
