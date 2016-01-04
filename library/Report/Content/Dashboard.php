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


namespace Report\Content;

class Dashboard extends \Report\Content {
    private $theme = null;

    public function collect() {
        if ($this->theme === null) { return true; }

        $groupBy = new \Report\Content\Groupby(null);
        $groupBy->addAnalyzer(\Analyzer\Analyzer::getThemeAnalyzers($this->theme));
        $groupBy->collect();
        $this->hasResults = $groupBy->hasResults();
        if (!$this->hasResults) {
            return true;
        }
        $this->array['upLeft'] = $groupBy;
        
        $infoBox = new \Report\Content\Infobox();
        $infoBox->setSeverities($groupBy->getArray());
        $infoBox->collect();
        $this->array['upRight'] = $infoBox;

        $listBySeverity = new \Report\Content\ListBySeverity();
        $listBySeverity->addAnalyzer(\Analyzer\Analyzer::getThemeAnalyzers($this->theme));
        $this->array['downLeft'] = $listBySeverity;

        $listByFile = new \Report\Content\ListByFile();
        $listByFile->addAnalyzer(\Analyzer\Analyzer::getThemeAnalyzers($this->theme));
        $this->array['downRight'] = $listByFile;

        return true;
    }
    
    public function setThema($theme) {
        $this->theme = $theme;
    }
}

?>
