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


$rows = glob('projects/*', GLOB_ONLYDIR);

$finals = [];
foreach($rows as $row) {
//    print $row."\n";
    
    $final = [basename($row)];

    $sqliteFilename = $row.'/datastore.sqlite';
    if (!file_exists($sqliteFilename)) {
        print "No $sqliteFilename\n";
        continue; 
    }
    
    $sqlite = new \Sqlite3($sqliteFilename);

    $res = $sqlite->querySingle('SELECT name FROM sqlite_master WHERE type="table" AND name="hash";');
    if ($res === null) {
        print "No hash table in $row\n";
        continue;
    }
    
    $res = $sqlite->query('SELECT * FROM hash WHERE key = "loc";');
    $sqlRow = $res->fetchArray();
    $final[] = $sqlRow['value'];

    $res = $sqlite->query('SELECT * FROM hash WHERE key = "tokens";');
    $sqlRow = $res->fetchArray();
    $final[] = $sqlRow['value'];
    
    $finals[] = $final;
}

$fp = fopen('size.csv', 'w+');
foreach($finals as $final) {
    fputcsv($fp, $final);
}
fclose($fp);

?>