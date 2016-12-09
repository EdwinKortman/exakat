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


namespace Exakat\Analyzer\Common;

use Exakat\Analyzer\Analyzer;

class Extension extends Analyzer {
    protected $source = '';
    
    public function dependsOn() {
        return array('Classes/ClassUsage',
                     'Interfaces/InterfaceUsage',
                     'Traits/TraitUsage',
                     'Constants/ConstantUsage',
                     'Namespaces/NamespaceUsage',
                     'Php/DirectivesUsage',
                     );
    }
    
    
    public function analyze() {
        $functions  = array();
        $constants  = array();
        $classes    = array();
        $interfaces = array();
        $traits     = array();
        $namespaces = array();
        $directives = array();

        if (substr($this->source, -4) == '.ini') {
            $ini = $this->loadIni($this->source);

            if (count($ini['functions']) == 1 && empty($ini['functions'][0])) {
                $functions = array();
            }

            if (count($ini['constants']) == 1 && empty($ini['constants'][0])) {
                $constants = array();
            }

            if (count($ini['classes']) == 1 && empty($ini['classes'][0])) {
                $classes = array();
            }

            if (count($ini['interfaces']) == 1 && empty($ini['interfaces'][0])) {
                $interfaces = array();
            }

            if (count($ini['traits']) == 1 && empty($ini['traits'][0])) {
                $traits = array();
            }

            if (count($ini['namespaces']) == 1 && empty($ini['namespaces'][0])) {
                $namespaces = array();
            }

            if (count($ini['directives']) == 1 && empty($ini['directives'][0])) {
                $directives = array();
            }
        } else {
            return true;
        }
        
        if (!empty($ini['functions'])) {
            $functions = $this->makeFullNsPath($ini['functions']);
            $this->atomIs('Functioncall')
                 ->hasNoIn('METHOD')
                 ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
                 ->fullnspathIs($functions);
            $this->prepareQuery();
        }
        
        if (!empty($ini['constants'])) {
            $this->atomIs('Identifier')
                 ->analyzerIs('Constants/ConstantUsage')
                 ->fullnspathIs($this->makeFullNsPath($ini['constants']));
            $this->prepareQuery();
        }

        if (!empty($ini['classes'])) {
            $classes = $this->makeFullNsPath($ini['classes']);

            $this->atomIs('New')
                 ->outIs('NEW')
                 ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
                 ->atomIsNot(array('Variable', 'Array', 'Property', 'Staticproperty', 'Methodcall', 'Staticmethodcall'))
                 ->fullnspathIs($classes);
            $this->prepareQuery();

            $this->atomIs('Staticconstant')
                 ->outIs('CLASS')
                 ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
                 ->fullnspathIs($classes);
            $this->prepareQuery();

            $this->atomIs('Staticmethodcall')
                 ->outIs('CLASS')
                 ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
                 ->fullnspathIs($classes);
            $this->prepareQuery();

            $this->atomIs('Staticproperty')
                 ->outIs('CLASS')
                 ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
                 ->fullnspathIs($classes);
            $this->prepareQuery();

            $this->atomIs('Function')
                 ->outIs('ARGUMENTS')
                 ->outIs('ARGUMENT')
                 ->outIs('TYPEHINT')
                 ->fullnspathIs($classes);
            $this->prepareQuery();

            $this->atomIs('Catch')
                 ->outIs('CLASS')
                 ->fullnspathIs($classes);
            $this->prepareQuery();

            $this->atomIs('Instanceof')
                 ->outIs('CLASS')
                 ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
                 ->atomIsNot(array('Array', 'Boolean', 'Null'))
                 ->fullnspathIs($classes);
            $this->prepareQuery();
        }

        if (!empty($interfaces)) {
            $interfaces = $this->makeFullNsPath($interfaces);
            $this->analyzerIs('Interfaces/InterfaceUsage')
                 ->fullnspathIs($ini['interfaces']);
            $this->prepareQuery();
        }

        if (!empty($traits)) {
            $this->analyzerIs('Traits/TraitUsage')
                 ->codeIs($traits);
            $this->prepareQuery();

            $traits = $this->makeFullNsPath($traits);
            $this->analyzerIs('Traits/TraitUsage')
                 ->fullnspathIs($ini['traits']);
            $this->prepareQuery();
        }

        if (!empty($namespaces)) {
            $namespaces = $this->makeFullNsPath($ini['namespaces']);
            $this->analyzerIs('Namespaces/NamespaceUsage')
                 ->outIs('NAME')
                 ->fullnspathIs($namespaces)
                 ->back('first');
            $this->prepareQuery();
            
            // Can a namespace be used in a nsname (as prefix) ? 
        }

        if (!empty($directives)) {
            $namespaces = $this->makeFullNsPath($ini['namespaces']);
            $this->analyzerIs('Php/DirectivesUsage')
                 ->outIs('ARGUMENTS')
                 ->outWithRank("ARGUMENT", 0)
                 ->noDelimiterIs($directives);
            $this->prepareQuery();
            
            // Can a namespace be used in a nsname (as prefix) ? 
        }
    }
}

?>
