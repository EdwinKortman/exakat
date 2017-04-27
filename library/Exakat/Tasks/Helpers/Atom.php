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

namespace Exakat\Tasks\Helpers;

use Exakat\Tasks\Load;

class Atom {
    static public $atomCount = 0;
    
    public $id           = 0;
    public $atom         = 'No Atom Set';
    public $code         = '';
    public $fullcode     = '';
    public $line         = Load::NO_LINE;
    public $token        = '';
    public $rank         = ''; // Not 0
    public $alternative  = Load::NOT_ALTERNATIVE;
    public $reference    = Load::NOT_REFERENCE;
    public $heredoc      = false;
    public $delimiter    = '';
    public $noDelimiter  = '';
    public $variadic     = Load::NOT_VARIADIC;
    public $count        = 0;
    public $fullnspath   = '';
    public $absolute     = Load::NOT_ABSOLUTE;
    public $alias        = '';
    public $origin       = '';
    public $encoding     = '';
    public $intval       = null;
    public $strval       = '';
    public $enclosing    = Load::NO_ENCLOSING;
    public $args_max     = '';
    public $args_min     = '';
    public $bracket      = Load::NOT_BRACKET;
    public $close_tag    = Load::NO_CLOSING_TAG;
    public $aliased      = Load::NOT_ALIASED;
    public $boolean      = 0;
    public $propertyname = '';
    public $constant     = Load::NOT_CONSTANT_EXPRESSION;
    public $root         = false;  // false is on purpose. 
    public $globalvar    = false;
    public $binaryString = Load::NOT_BINARY;

    public function __construct($atom) {
        $this->id = ++self::$atomCount;
        $this->atom = $atom;
    }
    
    public function toArray() {
        if (strlen($this->code) > 500) {
            $this->code = substr($this->code, 0, 500).'...[ total '.strlen($this->code).' chars]';
        }
        if (strlen($this->fullcode) > 500) {
            $this->fullcode = substr($this->fullcode, 0, 500).'...[ total '.strlen($this->fullcode).' chars]';
        }
        
        $this->code          = addcslashes($this->code       , '\\"');
        $this->fullcode      = addcslashes($this->fullcode   , '\\"');
        $this->fullnspath    = addcslashes($this->fullnspath , '\\"');
        $this->strval        = addcslashes($this->strval     , '\\"');
        $this->noDelimiter   = addcslashes($this->noDelimiter, '\\"');

        $this->alternative   = $this->alternative ? 'true' : 'false';
        $this->reference     = $this->reference   ? 'true' : 'false';
        $this->heredoc       = $this->heredoc     ? 'true' : 'false';
        $this->variadic      = $this->variadic    ? 'true' : 'false';
        $this->absolute      = $this->absolute    ? 'true' : 'false';
        $this->constant      = $this->constant    ? 'true' : 'false';
        $this->boolean       = $this->boolean     ? 'true' : 'false';
        $this->enclosing     = $this->enclosing   ? 'true' : 'false';
        $this->bracket       = $this->bracket     ? 'true' : 'false';
        $this->close_tag     = $this->close_tag   ? 'true' : 'false';
        $this->aliased       = $this->aliased     ? 'true' : 'false';
        
        return (array) $this;
    }

    public function toLimitedArray($headers) {
        $return = array();

        if (strlen($this->code) > 5000) {
            $this->code = substr($this->code, 0, 5000).'...[ total '.strlen($this->code).' chars]';
        }
        if (strlen($this->fullcode) > 5000) {
            $this->fullcode = substr($this->code, 0, 5000).'...[ total '.strlen($this->fullcode).' chars]';
        }

        $this->code          = addcslashes($this->code       , '\\"');
        $this->fullcode      = addcslashes($this->fullcode   , '\\"');
        $this->fullnspath    = addcslashes($this->fullnspath , '\\"');
        $this->strval        = addcslashes($this->strval     , '\\"');
        $this->noDelimiter   = addcslashes($this->noDelimiter, '\\"');

//'alternative', 'reference', 'heredoc', 'variadic', 'absolute','enclosing', 'bracket', 'close_tag', 'aliased', 'boolean'
        $this->alternative   = (int) $this->alternative;
        $this->reference     = (int) $this->reference  ;
        $this->heredoc       = (int) $this->heredoc    ;
        $this->variadic      = (int) $this->variadic   ;
        $this->absolute      = (int) $this->absolute   ;
        $this->constant      = (int) $this->constant   ;
        $this->boolean       = (int) $this->boolean    ;
        $this->enclosing     = (int) $this->enclosing  ;
        $this->bracket       = (int) $this->bracket    ;
        $this->close_tag     = (int) $this->close_tag  ;
        $this->aliased       = (int) $this->aliased    ;

        $return = array( $this->id,
                         $this->atom,
                         $this->code,
                         $this->fullcode,
                         $this->line,
                         $this->token,
                         $this->rank);
        
        foreach($headers as $head) {
            $return[] = $this->$head;
        }
        
        return $return;
    }
}

?>
