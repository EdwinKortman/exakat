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


namespace Analyzer;

class Docs {
    private $sqlite = null;
    private $phar_tmp = null;
    
    public function __construct($path) {
        if (substr($path, 0, 4) == 'phar') {
            $this->phar_tmp = tempnam(sys_get_temp_dir(), 'exDocs').'.sqlite';
            copy($path, $this->phar_tmp);
            $docPath = $this->phar_tmp;
        } else {
            $docPath = $path;
        }
        $this->sqlite = new \Sqlite3($docPath, SQLITE3_OPEN_READONLY);
    }

    public function __destruct() {
        if ($this->phar_tmp !== null) {
            unlink($this->phar_tmp);
        }
    }
    
    public function getThemeAnalyzers($theme = null) {
        if (is_array($theme)) {
            $theme = array_map(function ($x) { return trim($x, '"'); }, $theme);
            $where = 'WHERE c.name in ("'.join('", "', $theme).'")';
        } elseif ($theme === null) {
            // Default is ALL of them
            $where = '';
        } else {
            $where = 'WHERE c.name = "'.trim($theme, '"').'"';
        }

        $query = <<<SQL
        SELECT DISTINCT a.folder, a.name FROM analyzers AS a
    JOIN analyzers_categories AS ac
        ON ac.id_analyzer = a.id
    JOIN categories AS c
        ON c.id = ac.id_categories
    $where
SQL;
        
        $res = $this->sqlite->query($query);

        $return = array();
        while($row = $res->fetchArray()) {
            $return[] = $row['folder'].'/'.$row['name'];
        }
        
        return $return;
    }

    public function getThemeForAnalyzer($analyzer) {
        list($vendor, $class) = explode('/', $analyzer);
        
        $query = <<<SQL
SELECT c.name FROM categories AS c
    JOIN analyzers_categories AS ac
        ON ac.id_categories = c.id
    JOIN analyzers AS a
        ON a.id = ac.id_analyzer
    WHERE
        a.folder = '$vendor' AND
        a.name   = '$class'
SQL;
        $res = $this->sqlite->query($query);

        $return = array();
        while($row = $res->fetchArray()) {
            $return[] = $row['name'];
        }
        
        return $return;
    }
    
    public function getSeverity($analyzer) {
        list($folder, $name) = explode('\\', substr($analyzer, 9));
        $query = "SELECT severity FROM analyzers WHERE folder = '$folder' AND name = '$name'";

        $res = $this->sqlite->query($query);
        $res2 = $res->fetchArray();
        if (empty($res2[0])) {
            $return = \Analyzer\Analyzer::S_NONE;
        } else {
            $return = constant("\\Analyzer\\Analyzer::$res2[0]");
        }

        return $return;
    }

    public function getTimeToFix($analyzer) {
        list($folder, $name) = explode('\\', substr($analyzer, 9));
        $query = "SELECT timetofix FROM analyzers WHERE folder = '$folder' AND name = '$name'";

        $res = $this->sqlite->query($query);
        $res2 = $res->fetchArray();

        if (empty($res2[0])) {
            $return = \Analyzer\Analyzer::T_NONE;
        } else {
            $return = constant("\\Analyzer\\Analyzer::$res2[0]");
        }

        return $return;
    }

    public function getVendors() {
        $query = <<<SQL
        SELECT vendor FROM vendors
SQL;
        
        $res = $this->sqlite->query($query);

        $return = array();
        while($row = $res->fetchArray()) {
            $return[] = $row['vendor'];
        }
        
        return $return;
    }
    
    public function guessAnalyzer($name) {
        $query = <<<'SQL'
SELECT 'Analyzer\\' || folder || '\\' || name AS name FROM analyzers WHERE name=:name;

SQL;
        $stmt = $this->sqlite->prepare($query);

        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $res = $stmt->execute();

        $return = array();
        while($row = $res->fetchArray()) {
            $return[] = str_replace('\\\\', '\\', $row['name']);
        }
        
        return $return;
    }

    public function listAllAnalyzer($folder = null) {
        $query = <<<'SQL'
SELECT folder || '\\' || name AS name FROM analyzers

SQL;
        if ($folder !== null) {
            $query .= ' WHERE folder=:folder';
            $stmt = $this->sqlite->prepare($query);
            
            $stmt->bindValue(':folder', $folder, SQLITE3_TEXT);
        } else {
            $stmt = $this->sqlite->prepare($query);
        }
        $res = $stmt->execute();

        $return = array();
        while($row = $res->fetchArray()) {
            $return[] = str_replace('\\\\', '\\', $row['name']);
        }
        
        return $return;
    }
}
?>
