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


namespace Report\Format\Markdown;

class Row extends \Report\Format\Markdown {
    private $span = 6;
    
    public function render($output, $data) {
        // two columns are meaningless. We do one after each other
        $left = $data['left'];
        $right = $data['right'];
        
        if (is_object($left)) {
            $left->render($output);
        }

        if (is_object($right)) {
            $right->render($output);
        }
    }
    
    public function setSpan($span = 6) {
        $this->span = $span;
    }
}

?>
