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

class CodeSniffer extends Reports {
    const FILE_EXTENSION = 'txt';
    const FILE_FILENAME  = 'exakat';

    public function generate($folder, $name = self::FILE_FILENAME) {
        if ($this->config->thema !== NULL) {
            $list = Analyzer::getThemeAnalyzers(array($this->config->thema));
            $list = '"'.join('", "', $list).'"';
        } elseif ($this->config->program !== NULL) {
            print "analyze\n";
            $list = '"'.$this->config->program.'"';
        } else {
            print "default\n";
            $list = Analyzer::getThemeAnalyzers($this->themesToShow);
            $list = '"'.join('", "', $list).'"';
        }

        $sqlite = new \Sqlite3($folder.'/dump.sqlite');
        $sqlQuery = 'SELECT * FROM results WHERE analyzer in ('.$list.')';
        $res = $sqlite->query($sqlQuery);

        $results = array();
        $titleCache = array();
        $severityCache = array();
        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
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

        $separator = str_repeat('-', 80)."\n";
        $text = '';
        foreach($results as $file) {
            ksort($file['messages']);
            $text .= 'FILE : '.$file['filename']."\n";
            $text .= $separator;
            $c = count($file['messages']);
            $l = count(array_filter(array_unique(array_keys($file['messages'])), function ($x) { return $x > 0; }));
            $text .= 'FOUND '.$c.' ISSUE'.( $c > 1 ? 'S' : '').' AFFECTING '.$l.' LINE'.( $l > 1 ? 'S' : '')."\n";
            $text .= $separator;
            
            $maxSize = strlen(max(array_keys($file['messages'])));
            $padding = str_repeat(' ', $maxSize);

            $maxSize = strlen(max(array_keys($file['messages'])));
            $padding = str_repeat(' ', $maxSize);
            
            foreach($file['messages'] as $line => $column) {

                $messages = $column[0];
                foreach($messages as $message) {
                    $line = $line == -1 ? '  ' : $line;
                    $line = substr( $padding.$line, -$maxSize);
//                    var_dump($line);die();
                    $text .= ' '.$line.' | '.strtoupper($message['severity']).' | '.$message['message']."\n";
                    $this->count();
                }
            }
            $text .= $separator;
            $text .= "\n\n\n";
        }

        if ($name === 'stdout') {
            return $text;
        } else {
            file_put_contents($folder.'/'.$name.'.'.self::FILE_EXTENSION, $text);
            return true;
        }
    }
}

?>