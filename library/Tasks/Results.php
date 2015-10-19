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


namespace Tasks;

class Results extends Tasks {
    public function run(\Config $config) {
        $analyzer = $config->program;
        if (empty($analyzer)) {
            die('Provide the analyzer with the option -P X/Y. Aborting'."\n");
        }
        
        $analyzerClass = \Analyzer\Analyzer::getClass($analyzer);

        if ("Analyzer\\".str_replace('/', '\\', $analyzer) != $analyzerClass) {
            $die = "'$analyzer' doesn't exist. Aborting\n";
    
            $r = \Analyzer\Analyzer::getSuggestionClass($analyzer);
            if (count($r) > 0) {
                $die .= 'Did you mean : '.implode(', ', str_replace('_', '/', $r))."\n";
            }
            die($die);
        }

        $analyzer = str_replace('\\', '\\\\', $analyzerClass);

        $query = <<<GREMLIN
g.idx('analyzers')[['analyzer':'$analyzer']].out.map;
GREMLIN;

        $vertices = $this->query($query);
        if (isset($vertices[0]->notCompatibleWithPhpVersion)) {
            die($config->program." is not compatible with the running version of PHP. No result available.\n");
        }

        if (isset($vertices[0]->notCompatibleWithPhpConfiguration)) {
            die($config->program." is not compatible with the compilation of the running version of PHP. No result available.\n");
        }

        $return = array();
        if ($config->style == 'BOOLEAN') {
            $queryTemplate = 'g.idx("analyzers")[["analyzer":"'.$analyzer.'"]].out.any()';
            $vertices = $this->query($queryTemplate);

            $return[] = $vertices[0];
        } elseif ($config->style == 'COUNTED_ALL') {
            $queryTemplate = 'g.idx("analyzers")[["analyzer":"'.$analyzer.'"]].out.count()';
            $vertices = $this->query($queryTemplate);

            $return[] = $vertices[0];
        } elseif ($config->style == 'ALL') {
              $query = <<<GREMLIN
g.idx('analyzers')[['analyzer':'$analyzer']].out.sideEffect{m = ['Fullcode':it.fullcode, 'File':'None', 'Line':it.line, 'Namespace':'Globaln', 'Class':'Globalc', 'Function':'Globalf' ]; }.as('x')
                                            .transform{ it.in.loop(1){true}{ it.object.token in ['T_CLASS', 'T_FUNCTION', 'T_NAMESPACE', 'T_FILENAME']}.each{ m[it.atom] = it.code;} m; }.transform{ m; }
GREMLIN;

            $vertices = $this->query($query);

            $return = array();
            foreach($vertices as $k => $v) {
                $row = array($v->Fullcode,
                             $v->File,
                             $v->Line,
                             $v->Namespace,
                             $v->Class,
                             $v->Function);
                $return[] = $row;
            }
        } elseif ($config->style == 'DISTINCT') {
            $queryTemplate = 'g.idx("analyzers")[["analyzer":"'.$analyzer.'"]].out.code.unique()';
            $vertices = $this->query($queryTemplate);

            $return = array();
            foreach($vertices as $k => $v) {
                $return[] = $v[0];
            }
        } elseif ($config->style == 'COUNTED') {
            $queryTemplate = 'g.idx("analyzers")[["analyzer":"'.$analyzer.'"]].out.groupCount(m){it.code}.cap';
            $vertices = $this->query($queryTemplate);

            $return = array();
            foreach($vertices[0] as $k => $v) {
                $return[$k] = $v;
            }
        }

        if ($config->json === true) {
            $text = json_encode($return);
        } elseif ($config->csv === true) {
            $text = array(array('Code', 'File', 'Namespace', 'Class', 'Function'));
            foreach($return as $k => $v) {
                if (is_array($v)) {
                    $text[] = $v;
                } else {
                    $text[] = array($k, $v);
                }
            }
        } elseif ($config->markdown === true || $config->html === true || $config->odt === true) {
            $text = '';
            foreach($return as $k => $r) {
                if ($config->style == 'COUNTED') {
                    $text .= "+ $k => $r\n";
                } else {
                    $text .= "+ $k\n";
                    if (is_array($r)) {
                        $text .= '  + '.implode("\n  + ", $r)."\n";
                    } else {
                        $text .= "+ $r\n";
                    }
                }
            }
        } else {
            // count also for $config->text == 1
            $text = '';
            foreach($return as $k => $v) {
                if ($config->style == 'COUNTED') {
                    $text .= "$k => $v\n";
                } else {
                    $text .= implode(', ', $v)."\n";
                }
            }
        }

        if ($config->html === true || $config->odt === true) {
            $text = Markdown::defaultTransform($text);
        }

        if ($config->output) {
            echo $text;
        }

        switch (1) {
            case $config->json :
                $extension = 'json';
                break 1;
            case $config->odt :
                $extension = 'odt';
                break 1;
            case $config->markdown :
                $extension = 'md';
                break 1;
            case $config->html :
                $extension = 'html';
                break 1;
            case $config->csv :
                $extension = 'csv';
                break 1;
            case $config->text :
            default :
                $extension = 'txt';
                break 1;
        }

        if ($config->filename) {
            $name = $config->filename.'.'.$extension;
            if (file_exists($name)) {
                die( "$name already exists. Aborting\n");
            }

            if ($config->format == 'ODT') {
                $name1 = FILE.'.html';
                file_put_contents($name1, $text);

                $name = FILE.'.'.$extension;
                shell_exec('pandoc -o '.$name.' '.$name1);
                unlink($name1);
            } elseif ($config->format == 'CSV') {
                $csvFile = fopen($name, 'w');
                foreach($text as $t) {
                    fputcsv($csvFile, $t);
                }
                fclose($csvFile);
            } else {
                file_put_contents($name, $text);
            }
        }
    }

    private function query($query) {
        $result = gremlin_query($query);
        if (!isset($result->results)) {
            print $query;
            var_dump($result);
        }
        return (array) $result->results;
    }
}

?>
