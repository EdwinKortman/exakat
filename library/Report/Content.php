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


namespace Report;

class Content {
    protected $name       = 'Content'; 
    protected $project    = null;
    protected $array      = array();
    protected $hasResults = false;
    
    public function collect() {
        // By default, nothing to do.
    }

    public function setProject($project) {
        $this->project = $project;
    }
    
    public function getHash() {
        return $this->hash;
    }
    
    public function getArray() {
        return $this->array;
    }

    public function hasResults() {
        return $this->hasResults;
    }

    public function query($query) {
        $res = gremlin_query($query);
        
        return (array) $res->results;
    }
    
    // Make this a trait?
    public function loadJson($name) {
        $config = \Config::factory();
        $fullpath = $config->dir_root.'/data/'.$name.'.json';

        if (!file_exists($fullpath)) {
            return null;
        }

        $jsonFile = json_decode(file_get_contents($fullpath));
        
        return $jsonFile;
    }
}

?>
