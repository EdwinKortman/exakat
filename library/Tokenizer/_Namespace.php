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

class _Namespace extends TokenAuto {
    static public $operators = array('T_NAMESPACE');
    static public $atom = 'Namespace';

    public function _check() {
        // namespace {}
        $this->conditions = array(0 => array('token' => _Namespace::$operators,
                                             'atom'  => 'none'),
                                  1 => array('atom'  => 'Sequence',
                                             'property' => array('block' => true)),
                                  2 => array('token' => array('T_NAMESPACE', 'T_CLOSE_TAG', 'T_END', 'T_SEMICOLON')),
        );
        
        $this->actions = array('insertGlobalNs' => 1,
                               'keepIndexed'    => true);
        $this->checkAuto();

        // namespace myproject {}
        $this->conditions = array(0 => array('token' => _Namespace::$operators,
                                             'atom'  => 'none'),
                                  1 => array('atom'  => array('Identifier', 'Nsname')),
                                  2 => array('atom'  => 'Sequence'),
                                  3 => array('token' => array('T_NAMESPACE', 'T_CLOSE_TAG', 'T_END', 'T_SEMICOLON')),
        );
        
        $this->actions = array('transform'    => array( 1 => 'NAMESPACE',
                                                        2 => 'BLOCK'),
                               'atom'         => 'Namespace',
                               'cleanIndex'   => true,
                               'makeSequence' => 'it');
        $this->checkAuto();

        // namespace myproject ;
        $this->conditions = array(0 => array('token' => _Namespace::$operators,
                                             'atom'  => 'none'),
                                  1 => array('atom'  => array('Identifier', 'Nsname')),
                                  2 => array('token' => 'T_SEMICOLON',
                                             'atom'  => 'none'),
                                  3 => array('token' => array('T_CLOSE_TAG', 'T_END', 'T_SEMICOLON'),
                                             'atom'  => 'none')
        );
        
        $this->actions = array('insert_ns_void' => true,
                               'atom'           => 'Namespace',
                               'cleanIndex'     => true,
                               'makeSequence'   => 'it');
        $this->checkAuto();

        // namespace A; <Sequence> ? >
        $this->conditions = array(0 => array('token' => _Namespace::$operators,
                                             'atom'  => 'none'),
                                  1 => array('atom'  => array('Identifier', 'Nsname')),
                                  2 => array('token' => 'T_SEMICOLON'),
                                  3 => array('atom'  => 'Sequence'),
                                  4 => array('token' => array('T_CLOSE_TAG', 'T_END'))
        );
        
        $this->actions = array('insert_ns'    => true,
                               'atom'         => 'Namespace',
                               'cleanIndex'   => true,
                               'makeSequence' => 'it');
        $this->checkAuto();

        // namespace\Another : using namespace to build a namespace
        $this->conditions = array(0 => array('token' => _Namespace::$operators,
                                             'atom'  => 'none'),
                                  1 => array('token' => 'T_NS_SEPARATOR',
                                             'atom'  => 'none')
        );
        
        $this->actions = array('atom'         => 'Identifier',
                               'cleanIndex'   => true);
        $this->checkAuto();
        
        return false;
    }

    public function fullcode() {
        return <<<GREMLIN

fullcode.out("NAMESPACE").each{ fullcode.setProperty('fullcode', "namespace " + it.getProperty('fullcode'));}

fullcode.has('atom', 'Identifier').each{ fullcode.setProperty('fullcode', "namespace"); }

fullcode.has('fullcode', null).filter{ it.out('NAMESPACE').count() == 0}.each{ fullcode.setProperty('fullcode', "namespace Global");}

GREMLIN;
    }
}

?>
