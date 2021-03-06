<?php
/*
 * Copyright 2012-2018 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
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

namespace Exakat\Tasks\Helpers;

class Boolval extends Plugin {
    public $name = 'boolean';
    public $type = 'boolean';

    static public $PROP_BOOLVAL      = array('Integer', 'Boolean', 'Real', 'Null');
    
    public function run($atom, $extras) {
        foreach($extras as $extra) {
            if ($extra->boolean === '')  {
                $atom->boolean = '';
                return;
            }
        }

        switch ($atom->atom) {
            case 'Real' :
                // $atom->code is a string
                $atom->boolean = (int) (bool) (real) $atom->code;
                break;

            case 'Integer' :
                $atom->boolean = (int) (bool) (int) $atom->code;
                break;

            case 'Boolean' :
                $atom->boolean = (int) (mb_strtolower($atom->code) === 'true');
                break;

            case 'String' :
                $atom->boolean = (int) (trimOnce($atom->code) !== '');
                break;

            case 'Null' :
            case 'Void' :
                $atom->boolean = 0;
                break;
                
            case 'Parenthesis' :
                $atom->boolean = $extras['CODE']->boolean;
                break;
    
            case 'Addition' :
                if ($atom->code === '+') {
                    $atom->boolean = (int) (bool) ($extras['LEFT']->boolean + $extras['RIGHT']->boolean);
                } elseif ($atom->code === '-') {
                    $atom->boolean = (int) (bool) ($extras['LEFT']->boolean - $extras['RIGHT']->boolean);
                }
                break;

            case 'Multiplication' :
                if ($atom->code === '*') {
                    $atom->boolean = (int) (bool) ($extras['LEFT']->boolean * $extras['RIGHT']->boolean);
                } elseif ($atom->code === '/' && $extras['RIGHT']->boolean !== 0) {
                    $atom->boolean = (int) (bool) ($extras['LEFT']->boolean / $extras['RIGHT']->boolean);
                } elseif ($atom->code === '%' && $extras['RIGHT']->boolean !== 0) {
                    $atom->boolean = (int) (bool) ($extras['LEFT']->boolean % $extras['RIGHT']->boolean);
                }
                break;

            case 'Arrayliteral' :
                $atom->boolean    = (int) (bool) $atom->count;
                break;

            case 'Not' :
                if ($atom->code === '!') {
                    $atom->boolean = !$extras['NOT']->boolean;
                } elseif ($atom->code === '~') {
                    $atom->boolean = ~$extras['NOT']->boolean;
                }
                break;

            case 'Logical' :
                if ($atom->code === '|') {
                    $atom->boolean = $extras['LEFT']->boolean | $extras['RIGHT']->boolean;
                } elseif ($atom->code === '&') {
                    $atom->boolean = $extras['LEFT']->boolean & $extras['RIGHT']->boolean;
                } elseif ($atom->code === '^') {
                    $atom->boolean = $extras['LEFT']->boolean ^ $extras['RIGHT']->boolean;
                } elseif ($atom->code === '&&' || mb_strtolower($atom->code) === 'and') {
                    $atom->boolean = $extras['LEFT']->boolean && $extras['RIGHT']->boolean;
                } elseif ($atom->code === '||' || mb_strtolower($atom->code) === 'or') {
                    $atom->boolean = $extras['LEFT']->boolean && $extras['RIGHT']->boolean;
                } elseif (mb_strtolower($atom->code) === 'xor') {
                    $atom->boolean = $extras['LEFT']->boolean xor $extras['RIGHT']->boolean;
                } elseif ($atom->code === '<=>') {
                    $atom->boolean = $extras['LEFT']->boolean <=> $extras['RIGHT']->boolean;
                }
                break;

            case 'Concatenation' :
                $boolean = array_column($extras, 'boolean');
                $atom->boolean = (bool) implode('', $boolean);
                break;

            case 'Ternary' :
                if ($extras['CONDITION']->boolean) {
                    $atom->boolean = $extras['THEN']->boolean;
                } else {
                    $atom->boolean = $extras['ELSE']->boolean;
                }
                break;

            case 'Bitshift' :
                if ($atom->code === '>>') {
                    $atom->boolean = $extras['LEFT']->boolean >> $extras['RIGHT']->boolean;
                } elseif ($atom->code === '<<') {
                    $atom->boolean = $extras['LEFT']->boolean << $extras['RIGHT']->boolean;
                }
                break;

            case 'Comparison' :
                if ($atom->code === '==') {
                    $atom->boolean = $extras['LEFT']->boolean == $extras['RIGHT']->boolean;
                } elseif ($atom->code === '===') {
                    $atom->boolean = $extras['LEFT']->boolean === $extras['RIGHT']->boolean;
                } elseif ($atom->code === '!=' || $atom->code === '<>') {
                    $atom->boolean = $extras['LEFT']->boolean != $extras['RIGHT']->boolean;
                } elseif ($atom->code === '!==') {
                    $atom->boolean = $extras['LEFT']->boolean !== $extras['RIGHT']->boolean;
                } elseif ($atom->code === '>') {
                    $atom->boolean = $extras['LEFT']->boolean > $extras['RIGHT']->boolean;
                } elseif ($atom->code === '<') {
                    $atom->boolean = $extras['LEFT']->boolean < $extras['RIGHT']->boolean;
                } elseif ($atom->code === '>=') {
                    $atom->boolean = $extras['LEFT']->boolean >= $extras['RIGHT']->boolean;
                } elseif ($atom->code === '<=') {
                    $atom->boolean = $extras['LEFT']->boolean <= $extras['RIGHT']->boolean;
                }
                break;

        default :
            
        }
    }
}

?>
