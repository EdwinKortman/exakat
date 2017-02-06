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

namespace Exakat\Analyzer\Classes;

use Exakat\Analyzer\Analyzer;

class CouldBeProtectedProperty extends Analyzer {
    public function analyze() {
        // Case of property->property (that's another public access)
        $publicProperties = $this->query('g.V().hasLabel("Property")
                                              .where( __.out("OBJECT").not(has("code", "\$this")) )
                                              .out("PROPERTY")
                                              .hasLabel("Identifier")
                                              .values("code").unique()');
        
        if (!empty($publicProperties)) {
            // Property that is not used outside this class or its children
            $this->atomIs('Ppp')
                 ->hasNoOut(array('PROTECTED', 'PRIVATE'))
                 ->hasNoOut('STATIC')
                 ->outIs('PPP')
                 ->isNot('propertyname', $publicProperties);
            $this->prepareQuery();
        }
        
        // Case of property::property (that's another public access)
        $res = $this->query('g.V().hasLabel("Staticproperty").as("init")
                                  .out("CLASS").hasLabel("Identifier", "Nsname")
                                  .not(has("code", within("self", "static"))).as("classe")
                                  .sideEffect{ fnp = it.get().value("fullnspath") }
                                  .in("CLASS")
                                  .where( __.repeat( __.in('.$this->linksDown.')).until(hasLabel("Class", "File"))
                                            .or(hasLabel("File"), 
                                                hasLabel("Class").filter{ it.get().values("fullnspath") == fnp; }) 
                                        )
                                  .out("PROPERTY").hasLabel("Variable").as("variable")
                                  .select("classe", "variable").by("fullnspath").by("code")
                                  .unique();
                                  ');

        $publicStaticProperties = array();
        foreach($res as $value) {
            if (isset($publicStaticProperties[$value->classe])) {
                $publicStaticProperties[$value->classe][] = $value->variable;
            } else {
                $publicStaticProperties[$value->classe] = array($value->variable);
            }
        }
        
        if (!empty($publicStaticProperties)) {
            // Property that is not used outside this class or its children
            $this->atomIs('Ppp')
                 ->hasNoOut(array('PROTECTED', 'PRIVATE'))
                 ->hasOut('STATIC')
                 ->goToClass()
                 ->savePropertyAs('fullnspath', 'fnp')
                 ->back('first')
                 ->outIs('PPP')
                 ->outIsIE('LEFT')
                 ->isNotHash('code', $publicStaticProperties, 'fnp')
                 ->inIsIE('LEFT');
            $this->prepareQuery();
        }
    }
}

?>
