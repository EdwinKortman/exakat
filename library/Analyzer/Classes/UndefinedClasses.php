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


namespace Analyzer\Classes;

use Analyzer;

class UndefinedClasses extends Analyzer\Analyzer {
    public function dependsOn() {
        return array('Analyzer\\Classes\\IsExtClass',
                     'Analyzer\\Composer\\IsComposerNsname');
    }
    
    public function analyze() {
        // in a New
        $this->atomIs('New')
             ->outIs('NEW')
             ->analyzerIsNot('Analyzer\\Composer\\IsComposerNsname')
             ->tokenIsNot(array('T_VARIABLE', 'T_OPEN_BRACKET'))
             ->codeIsNot(array('self', 'parent', 'static'))
             ->analyzerIsNot('Analyzer\\Classes\\IsExtClass')
             ->noClassDefinition()
             ->back('first');
        $this->prepareQuery();

        // in a class::Method()
        $this->atomIs('Staticmethodcall')
             ->analyzerIsNot('Analyzer\\Composer\\IsComposerNsname')
             ->outIs('CLASS')
             ->tokenIsNot(array('T_VARIABLE', 'T_OPEN_BRACKET'))
             ->codeIsNot(array('self', 'parent', 'static'))
             ->analyzerIsNot('Analyzer\\Classes\\IsExtClass')
             ->noClassDefinition()
             ->noInterfaceDefinition()
             ->noTraitDefinition()
             ->back('first');
        $this->prepareQuery();

        // in a parent::Method()
        $this->atomIs('Staticmethodcall')
             ->analyzerIsNot('Analyzer\\Composer\\IsComposerNsname')
             ->outIs('CLASS')
             ->tokenIsNot(array('T_VARIABLE', 'T_OPEN_BRACKET'))
             ->code('parent')
             ->fullnspath('parent')
             ->back('first');
        $this->prepareQuery();

        // in a class::$property
        $this->atomIs('Staticproperty')
             ->analyzerIsNot('Analyzer\\Composer\\IsComposerNsname')
             ->outIs('CLASS')
             ->tokenIsNot(array('T_VARIABLE', 'T_OPEN_BRACKET'))
             ->codeIsNot(array('self', 'parent', 'static'))
             ->analyzerIsNot('Analyzer\\Classes\\IsExtClass')
             ->noClassDefinition()
             ->noInterfaceDefinition()
             ->noTraitDefinition()
             ->back('first');
        $this->prepareQuery();

        // in a parent::$property
        $this->atomIs('Staticproperty')
             ->outIs('CLASS')
             ->tokenIsNot(array('T_VARIABLE', 'T_OPEN_BRACKET'))
             ->code('parent')
             ->fullnspath('parent')
             ->back('first');
        $this->prepareQuery();

        // in a class::constante
        $this->atomIs('Staticconstant')
             ->analyzerIsNot('Analyzer\\Composer\\IsComposerNsname')
             ->outIs('CLASS')
             ->tokenIsNot(array('T_VARIABLE', 'T_OPEN_BRACKET'))
             ->codeIsNot(array('self', 'parent', 'static'))
             ->analyzerIsNot('Analyzer\\Classes\\IsExtClass')
             ->noClassDefinition()
             ->noInterfaceDefinition()
             ->noTraitDefinition()
             ->back('first');
        $this->prepareQuery();

        // in a parent::constante
        $this->atomIs('Staticconstant')
             ->analyzerIsNot('Analyzer\\Composer\\IsComposerNsname')
             ->outIs('CLASS')
             ->tokenIsNot(array('T_VARIABLE', 'T_OPEN_BRACKET'))
             ->code('parent')
             ->fullnspath('parent')
             ->back('first');
        $this->prepareQuery();

        // in a class::instanceof
        $this->atomIs('Instanceof')
             ->analyzerIsNot('Analyzer\\Composer\\IsComposerNsname')
             ->outIs('CLASS')
             ->tokenIsNot(array('T_VARIABLE', 'T_OPEN_BRACKET'))
             ->codeIsNot(array('self', 'parent', 'static'))
             ->analyzerIsNot('Analyzer\\Classes\\IsExtClass')
             ->analyzerIsNot('Analyzer\\Interfaces\\IsExtInterface')
             ->noClassDefinition()
             ->noInterfaceDefinition()
             ->noTraitDefinition()
             ->back('first');
        $this->prepareQuery();
    }
}

?>
