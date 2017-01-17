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

namespace Exakat\Reports;

use Exakat\Analyzer\Analyzer;

class Text extends Reports {
    const FILE_EXTENSION = 'txt';
    const FILE_FILENAME  = 'exakat';

    private $themesToShow = array('CompatibilityPHP53', 'CompatibilityPHP54', 'CompatibilityPHP55', 'CompatibilityPHP56', 
                                  'CompatibilityPHP70', 'CompatibilityPHP71',
                                  '"Dead code"', 'Security', 'Analyze');

    public function generate($folder, $name = self::FILE_FILENAME) {
        $list = Analyzer::getThemeAnalyzers($this->themesToShow);
        $list = '"'.join('", "', $list).'"';

        $sqlite = new \Sqlite3($folder.'/dump.sqlite');
        $sqlQuery = 'SELECT * FROM results WHERE analyzer in ('.$list.')';
        $res = $sqlite->query($sqlQuery);
        
        $results = array();
        $titleCache = array();
        $severityCache = array();
        while($row = $res->fetchArray(SQLITE3_ASSOC)) {
            if (!isset($results[$row['file']])) {
                $file = array('errors'   => 0,
                              'warnings' => 0,
                              'fixable'  => 0,
                              'filename' => $row['file'],
                              'messages' => array());
                $results[$row['file']] = $file;
            }

            if (!isset($titleCache[$row['analyzer']])) {
                $analyzer = Analyzer::getInstance($row['analyzer']);
                $titleCache[$row['analyzer']] = $analyzer->getDescription()->getName();
                $severityCache[$row['analyzer']] = $analyzer->getSeverity();
            }

            $message = array('type'     => 'warning',
                             'source'   => $row['analyzer'],
                             'severity' => $severityCache[$row['analyzer']],
                             'fixable'  => 'fixable',
                             'message'  => $titleCache[$row['analyzer']]);

            if (!isset($results[ $row['file'] ]['messages'][ $row['line'] ])) {
                $results[ $row['file'] ]['messages'][ $row['line'] ] = array(0 => array());
            }
            $results[ $row['file'] ]['messages'][ $row['line'] ][0][] = $message;

            ++$results[ $row['file'] ]['warnings'];
        }
        
        $text = '';
        foreach($results as $file) {
            foreach($file['messages'] as $line => $column) {
                $messages = $column[0];
                foreach($messages as $message) {
                    $text .= $file['filename'].':'.$line.' '.$message['message']."\n";
                    $this->count();
                }
            }
        }
        
        if ($name === null) {
            return $text;
        } else {
            file_put_contents($folder.'/'.$name.'.'.self::FILE_EXTENSION, $text);
            return true;
        }
    }
}

?>