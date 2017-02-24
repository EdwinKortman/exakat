<?php
/*
 * Copyright 2012-2017 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
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


namespace Exakat\Analyzer\Constants;

use Exakat\Analyzer\Analyzer;

class MultipleConstantDefinition extends Analyzer {
    public function analyze() {
        // case-insensitive constants with Define
        // Search for definitions and count them
        $csDefinitions = $this->query(<<<GREMLIN
g.V().hasLabel("Functioncall").where( __.in("METHOD", "NEW").count().is(eq(0)) )
                              .where( __.out("NAME").hasLabel("Array", "Variable").count().is(eq(0)))
                              .has("token", within('T_STRING', 'T_NS_SEPARATOR', 'T_ARRAY') )
                              .filter{it.get().value("fullnspath").toLowerCase() == '\\\\define'}
                              .out("ARGUMENTS")
                              .or( __.out("ARGUMENT").has("rank", 2).count().is(eq(0)),
                                   __.out("ARGUMENT").has("rank", 2).has('boolean', false),
                                  )
                              .out("ARGUMENT").has("rank", 0).hasLabel("String").where(__.out("CONCAT").count().is(eq(0)) )
                              .values("noDelimiter")
GREMLIN
);

        $constDefinitions = $this->query(<<<GREMLIN
g.V().hasLabel("Const").where( __.in("ELEMENT").in("BLOCK").hasLabel("Class", "Trait").count().is(eq(0)) )
                       .out("CONST")
                       .out("NAME").values("code")
GREMLIN
);

        $cisDefinitions = $this->query(<<<GREMLIN
g.V().hasLabel("Functioncall").where( __.in("METHOD", "NEW").count().is(eq(0)) )
                              .has("token", within('T_STRING', 'T_NS_SEPARATOR', 'T_ARRAY') )
                              .where( __.out("NAME").hasLabel("Array", "Variable").count().is(eq(0)))
                              .filter{it.get().value("fullnspath").toLowerCase() == '\\\\define'}
                              .out("ARGUMENTS")
                              .out("ARGUMENT").has("rank", 2).has("boolean", true).in("ARGUMENT")
                              .out("ARGUMENT").has("rank", 0)
                              .map{ it.get().value("noDelimiter").toLowerCase()}
GREMLIN
);
        if ($a = $this->selfCollisions($cisDefinitions)) {
            $this->applyToCisDefine($a);
        }

        if ($a = $this->selfCollisions(array_merge($constDefinitions, $csDefinitions))) {
            $this->applyToConst(array_intersect($a, $constDefinitions));
            $this->applyToCsDefine(array_intersect($a, $csDefinitions));
        }
        
        if ($a = $this->CsCisCollisions($csDefinitions, $cisDefinitions)) {
            $this->applyToCisDefine($a);
            $this->applyToCsDefine($a);
        }

        if ($a = $this->CsCisCollisions($constDefinitions, $cisDefinitions)) {
            $this->applyToCisDefine($a);
            $this->applyToConst($a);
        }
    }
    
    private function selfCollisions($array) {
        // two definitions are case sensitive
        return array_keys(array_filter(array_count_values($array), function ($x) { return $x > 1; }));
    }
    
    private function CsCisCollisions($csDefinitions, $cisDefinitions) {
        return array_merge( array_intersect($csDefinitions, $cisDefinitions),
                            array_intersect($csDefinitions, array_map(function ($x) { return strtoupper($x); }, $cisDefinitions) ) );
    }
    
    private function applyToCisDefine($array) {
        if (empty($array)) {
            return;
        }
        
        $this->atomFunctionIs('\\define')
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 2)
             ->is('boolean', true)
             ->inIs('ARGUMENT')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->hasNoOut('CONCAT')
             ->noDelimiterIs($array);
        $this->prepareQuery();
    }

    private function applyToCsDefine($array) {
        if (empty($array)) {
            return;
        }
        $this->atomFunctionIs('\\define')
             ->outIs('ARGUMENTS')
             ->outWithRank('ARGUMENT', 2)
             ->is('boolean', false)
             ->inIs('ARGUMENT')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->hasNoOut('CONCAT')
             ->noDelimiterIs($array);
        $this->prepareQuery();

        $this->atomFunctionIs('\\define')
             ->outIs('ARGUMENTS')
             ->noChildWithRank('ARGUMENT', 2)
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->hasNoOut('CONCAT')
             ->noDelimiterIs($array);
        $this->prepareQuery();
    }

    private function applyToConst($array) {
        if (empty($array)) {
            return;
        }

        $this->atomIs('Const')
             ->hasNoClassTrait()
             ->outIs('CONST')
             ->outIs('NAME')
             ->codeIs($array);
        $this->prepareQuery();
    }

}

?>
