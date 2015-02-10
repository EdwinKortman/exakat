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


namespace Report\Content;

class AnalyzerConfig extends \Report\Content {
    private $analyzer = 'No Analyzer';
    
    public function collect() {
        $config = \Config::factory();
        $analyzer = str_replace('/', '_', $this->analyzer);
        
        $list = $config->$analyzer;
        if (is_array($list)) {
            $this->list = array_flip($list);
        } else {
            $this->list = array();
        }
    }

    public function setAnalyzer($analyzer) {
        $this->analyzer = $analyzer;
    }

    public function getArray() {
        $return = array();
        foreach($this->list as $k => $v) {
            $return[] = array($k, $v);
        }
        return $return;
    }

}

?>
