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
use Exakat\Data\Methods;

class IsRead extends Analyzer\Analyzer {
    public function dependsOn() {
        return array('Classes/Constructor');
    }
    
    public function analyze() {
        $this->atomIs('Variable')
             ->hasIn(array('NOT', 'AT', 'OBJECT', 'NEW', 'RETURN', 'CONCAT', 'SOURCE', 'CODE', 'INDEX', 'CONDITION', 'THEN', 'ELSE',
                           'KEY', 'VALUE', 'NAME', 'DEFINE', 'PROPERTY', 'METHOD', 'VARIABLE', 'SIGN', 'THROW', 'CAST',
                           'CASE', 'CLONE', 'FINAL', 'CLASS', 'GLOBAL', 'PPP'));
        $this->prepareQuery();

        // Reading inside an assignation
        $this->atomIs('Variable')
             ->inIs('LEFT')
             ->atomIs('Assignation')
             ->codeIs('=')
             ->hasIn(array('NOT', 'AT', 'OBJECT', 'NEW', 'RETURN', 'CONCAT', 'SOURCE', 'CODE', 'INDEX', 'CONDITION', 'THEN', 'ELSE',
                           'KEY', 'VALUE', 'NAME', 'DEFINE', 'PROPERTY', 'METHOD', 'VARIABLE', 'SIGN', 'THROW', 'CAST',
                           'CASE', 'CLONE', 'FINAL', 'CLASS', 'PPP'))
             ->back('first');
            // note : NAME is for Switch!!
        $this->prepareQuery();

        // $this is always read
        $this->atomIs('Variable')
             ->codeIs('$this');
        $this->prepareQuery();
             

        // right or left, same
        $this->atomIs('Variable')
             ->inIs(array('RIGHT', 'LEFT'))
             ->atomIs(array('Addition', 'Multiplication', 'Logical', 'Comparison', 'Bitshift'))
             ->back('first');
        $this->prepareQuery();

        // right only
        $this->atomIs('Variable')
             ->inIs('RIGHT')
             ->atomIs('Assignation')
             ->back('first');
        $this->prepareQuery();

        // $x++ + 2 (a plusplus within another
        $this->atomIs('Variable')
             ->inIs(array('PREPLUSPLUS', 'POSTPLUSPLUS'))
             ->inIs(array('RIGHT', 'LEFT'))
             ->atomIs(array('Addition', 'Multiplication', 'Logical', 'Comparison', 'Bitshift', 'Assignation'))
             ->back('first');
        $this->prepareQuery();

        // $x++ + 2 (a plusplus in a functioncall
        $this->atomIs('Variable')
             ->inIs(array('PREPLUSPLUS', 'POSTPLUSPLUS'))
             ->hasIn('ARGUMENT')
             ->back('first');
        $this->prepareQuery();

        // variable in a sequence (also useless...)
        $this->atomIs('Variable')
             ->inIs('ELEMENT')
             ->atomIs('Sequence')
             ->back('first');
        $this->prepareQuery();

        // array only
        $this->atomIs('Variable')
             ->inIs('VARIABLE')
             ->atomIs(array('Array', 'Arrayappend'))
             ->back('first');
        $this->prepareQuery();

        // arguments
        $this->atomIs('Function')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->atomIs('VARIABLE');
        $this->prepareQuery();

        // arguments : instanceof
        $this->atomIs('Instanceof')
             ->outIs('LEFT')
             ->atomIs('VARIABLE')
             ->back('first');
        $this->prepareQuery();

        // arguments : normal variable in a custom function
        $this->atomIs('Variable')
             ->savePropertyAs('rank', 'rank')
             ->inIs('ARGUMENT')
             ->inIs('ARGUMENTS')
             ->hasNoIn('METHOD') // possibly new too
             ->functionDefinition()
             ->inIs('NAME')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->samePropertyAs('rank', 'rank', true)
             ->isNot('reference', true)
             ->back('first');
        $this->prepareQuery();

        // PHP functions that are passed by value
        $data = new Methods();
        
        $functions = $data->getFunctionsValueArgs();
        $references = array();
        
        foreach($functions as $function) {
            if (!isset($references[$function['position']])) {
                $references[$function['position']] = array('\\'.$function['function']);
            } else {
                $references[$function['position']][] = '\\'.$function['function'];
            }
        }
        
        foreach($references as $position => $functions) {
            $this->atomIs('Variable')
                 ->is('rank', $position)
                 ->inIs('ARGUMENT')
                 ->inIs('ARGUMENTS')
                 ->atomIs('Functioncall')
                 ->hasNoIn('METHOD')
                 ->tokenIs(array('T_STRING','T_NS_SEPARATOR'))
                 ->fullnspathIs($functions)
                 ->back('first');
            $this->prepareQuery();
        }

        // Variable that are not a reference in a functioncall
        $this->atomIs('Variable')
             ->hasIn('ARGUMENT')
             ->hasNoParent('Function', array('ARGUMENT', 'ARGUMENTS'))
             ->analyzerIsNot('self');
        $this->prepareQuery();

    }
}

?>
