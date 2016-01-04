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


namespace Report\Format\Html;

class Summary extends \Report\Format\Html { 
    public function render($output, $data) {
        $text = $this->render2($data);

        $text = <<<TEXT
$text

TEXT;

        $output->push("$text\n");
    }

    private function render2($data) {
        $text = '<ul>';
        foreach($data as $row) {
            if (get_class($row) != "Report\\Template\\Section") { continue; }
            if ($row->getName() == "Summary") { continue; }
            $text .= "<li>".$row->getName()."</li>\n";

            $text .= $this->render2($row->getSections());
        }
        
        $text .= "</ul>";
        
        return $text; 
    }
}

?>
