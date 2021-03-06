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


namespace Exakat\Reports;

use XmlWriter;
use Exakat\Analyzer\Analyzer;
use Exakat\Exakat;
use Exakat\Reports\Helpers\Results;

class Favorites extends Reports {
    private $cachedData = '';

    const FILE_EXTENSION = 'json';
    const FILE_FILENAME  = 'favorites';

    public function _generate($analyzerList) {
        $analyzers = $this->themes->getThemeAnalyzers('Preferences');
        
        $return = array();
        foreach($analyzers as $analyzer) {
            $r = $this->datastore->getHashAnalyzer($analyzer);
            if (empty($r)) { 
                continue; 
            }
            $return[$analyzer] = $r;
        }
        
        return json_encode($return, JSON_PRETTY_PRINT);
    }
}

?>