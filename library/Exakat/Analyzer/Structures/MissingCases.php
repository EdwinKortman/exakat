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

namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;

class MissingCases extends Analyzer {
    /* Remove this if useless
    public function dependsOn() {
        return array('MethodDefinition');
    }
    */
    
    public function analyze() {
        $switches = $this->query(<<<GREMLIN
g.V().hasLabel("Switch").sideEffect{ x = []; }.sideEffect( __.out('CASES').out('ELEMENT').out('CASE').hasLabel('String').not(where(out("CONCAT"))).sideEffect{x.add(it.get().value('noDelimiter'));}).filter{x.size() > 0;}.map{x;}
GREMLIN
);
        if (empty($switches)) {
            return;
        }
        
        /*
        $arrays = $this->query(<<<GREMLIN
g.V().hasLabel("Functioncall").has('fullnspath', '\\\\array').sideEffect{ x = []; }.sideEffect( __.out('ARGUMENTS').out('ARGUMENT')
                              .coalesce(hasLabel('String').not(where(out("CONCAT"))), 
                                        hasLabel('Keyvalue').out('VALUE').hasLabel('String').not(where(out("CONCAT")))
                               ).sideEffect{x.add(it.get().value('noDelimiter'));}).filter{ x.size() > 0;}.map{x;}.unique()
GREMLIN
);
        if (empty($arrays)) {
            return;
        }
        */

        // Compare switches together. 
        $commons = array();
        foreach($switches as $s) {
            foreach($switches as $a) {
                $diff = array_intersect($a, $s);
                // No common ground
                if (empty($diff)) { continue; }
                $diff = array_merge( array_diff($s, $a), array_diff($a, $s));
                // No differences between the lists
                if (empty($diff)) { continue; }

                // Estimation of the common elements
                $score = 100 * count($diff) / count(array_unique(array_merge($a, $s)));
                if ($score > 25) { continue; }

//                print "Diff / total ".number_format($score)."\n";
//                print "Common / total ".number_format(100 * count(array_intersect($a, $s)) / count(array_unique(array_merge($a, $s))))."\n";
                $commons[] = $s;
                $commons[] = $a;
            }
        }

        if (empty($commons)) {
            return;
        }

        foreach($commons as &$s) {
            $s = '["'.implode('", "', $s).'"]'; 
        }
        unset($s);
        $commons = '['.implode(', ', $commons).']';

        $this->atomIs('Switch')
             ->raw('sideEffect{ x = []; }.sideEffect( __.out("CASES").out("ELEMENT").out("CASE").hasLabel("String").not(where(out("CONCAT"))).sideEffect{x.add(it.get().value("noDelimiter"));}).filter{x.size() > 0;}.map{x;}')
             ->raw('filter{ x in '.$commons.' }')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
