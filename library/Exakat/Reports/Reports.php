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

use Exakat\Config;
use Exakat\Analyzer\Themes;
use Exakat\Analyzer\Analyzer;
use Exakat\Datastore;
use Exakat\Dump;

abstract class Reports {
//    const FILE_EXTENSION = 'undefined';
//    const FILE_NAME      = 'undefined';
    
    const STDOUT = 'stdout';
    const INLINE = 'inline';

    static public $FORMATS        = array('Ambassador', 'AmbassadorNoMenu', 'Drillinstructor',
                                          'Text', 'Xml', 'Uml', 'PlantUml', 'None', 'SimpleHtml', 'Owasp',
                                          'PhpConfiguration', 'PhpCompilation', 'Favorites', 'Manual',
                                          'Inventories', 'Clustergrammer', 'FileDependencies', 'FileDependenciesHtml',
                                          'ZendFramework',  'CodeSniffer', 'Slim',
                                          'RadwellCode', 'Melis', 'Grade', 'Weekly', 'Codacy', 'Scrutinizer', 
                                          'FacetedJson', 'Json', 'OnepageJson', 'Marmelab', 'Simpletable',
                                          'Codeflower', 'Dependencywheel',
                                          );

    protected $themesToShow = array('CompatibilityPHP56', //'CompatibilityPHP53', 'CompatibilityPHP54', 'CompatibilityPHP55',
                                    'CompatibilityPHP70', 'CompatibilityPHP71', 'CompatibilityPHP72', 'CompatibilityPHP73',
                                    '"Dead code"', 'Security', 'Analyze', 'Inventories');

    private $count = 0;

    protected $themesList = '';      // cache for themes list in SQLITE
    protected $config     = null;

    protected $sqlite = null;
    protected $datastore = null;
    protected $themes = null;

    public function __construct($config) {
        $this->config = $config;

        $this->sqlite = new \Sqlite3($this->config->projects_root.'/projects/'.$this->config->project.'/dump.sqlite', \SQLITE3_OPEN_READONLY);

        $this->datastore = new Dump($this->config);
        $this->themes = new Themes($this->config->dir_root.'/data/analyzers.sqlite');

        $analyzers = $this->themes->getThemeAnalyzers($this->config->thema);
        $this->themesList = '("'.implode('", "', $analyzers).'")';
    }

    protected function _generate($analyzerList) {}
    
    public function generate($folder, $name) {
        if (empty($name)) {
            // FILE_FILENAME is defined in the children class
            $name = $this::FILE_FILENAME;
        }

        if ($this->config->thema !== null) {
            $list = $this->themes->getThemeAnalyzers(array($this->config->thema));
        } elseif ($this->config->program !== null) {
            $list = $this->config->program;
        } else {
            $list = $this->themes->getThemeAnalyzers($this->themesToShow);
        }

        $final = $this->_generate($list);

        if ($name === self::STDOUT) {
            echo $final ;
        } elseif ($name === self::INLINE) {
            return $final ;
        } else {
            file_put_contents($folder.'/'.$name.'.'.$this::FILE_EXTENSION, $final);
        }
    }

    protected function count($step = 1) {
        $this->count += $step;
    }

    public function getCount() {
        return $this->count;
    }
}

?>