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


namespace Analyzer\Files;

use Analyzer;

class Services extends Analyzer\Analyzer {
    private $report = null;
    
    public function analyze() {
        // Just a place holder
        return true;
    }

    public function toArray() {
        if ($this->report === null) {
            $this->hasResults();
        }
        
        return $this->report;
    }

    public function hasResults() {
        if ($this->report === null) {
            $this->report = \Analyzer\Analyzer::$datastore->getRow('configFiles');
        }
        
        return count($this->report) > 0;
    }
}

?>
