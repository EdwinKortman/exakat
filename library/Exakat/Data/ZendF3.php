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


namespace Exakat\Data;

use Exakat\Config;

class ZendF3 {
    protected $sqlite = null;
    protected $phar_tmp = null;

    public function __construct($path, $isPhar) {
        $config = Config::factory();

        if ($isPhar) {
            $this->phar_tmp = tempnam(sys_get_temp_dir(), 'exzendf3').'.sqlite';
            copy($path.'/zendf3.sqlite', $this->phar_tmp);
            $docPath = $this->phar_tmp;
        } else {
            $docPath = $path.'/zendf3.sqlite';
        }
        $this->sqlite = new \Sqlite3($docPath, \SQLITE3_OPEN_READONLY);
    }

    public function __destruct() {
        if ($this->phar_tmp !== null) {
            unlink($this->phar_tmp);
        }
    }

    public function getClasses($component, $release = null) {
        $query = 'SELECT namespaces.namespace || "\" || class AS class, release FROM classes 
                    JOIN namespaces 
                      ON classes.namespace_id = namespaces.id
                    JOIN releases 
                      ON namespaces.release_id = releases.id 
                    JOIN components 
                      ON releases.component_id = components.id 
                    WHERE components.component = "'.$component.'"';
        if ($release !== null) {
            $query .= " AND releases.release = \"release-$release.0\"";
        }

        $res = $this->sqlite->query($query);

        $return = array();
        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            if (isset($return[$row['release']])) {
                $return[$row['release']][] = $row['class'];
            } else {
                $return[$row['release']] = array($row['class']);
            }
        }

        return $return;
    }

    public function getInterfaces($component, $release = null) {
        $query = 'SELECT namespaces.namespace || "\" || interface AS interface, release FROM interfaces 
                    JOIN namespaces 
                      ON interfaces.namespace_id = namespaces.id
                    JOIN releases 
                      ON namespaces.release_id = releases.id
                    JOIN components 
                      ON releases.component_id = components.id 
                    WHERE components.component = "'.$component.'"';
        $res = $this->sqlite->query($query);
        $return = array();

        if ($release !== null) {
            $query .= " AND releases.release = \"release-$release.0\"";
        }

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            if (isset($return[$row['release']])) {
                $return[$row['release']][] = $row['interface'];
            } else {
                $return[$row['release']] = array($row['interface']);
            }
        }

        return $return;
    }

    public function getTraits($component, $release = null) {
        $query = 'SELECT namespaces.namespace || "\" || trait AS trait, release FROM traits 
                    JOIN namespaces 
                      ON traits.namespace_id = namespaces.id
                    JOIN releases 
                      ON namespaces.release_id = releases.id
                    JOIN components 
                      ON releases.component_id = components.id 
                    WHERE components.component = "'.$component.'"';
        $res = $this->sqlite->query($query);
        $return = array();

        if ($release !== null) {
            $query .= " AND releases.release = \"release-$release.0\"";
        }

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            if (isset($return[$row['release']])) {
                $return[$row['release']][] = $row['trait'];
            } else {
                $return[$row['release']] = array($row['trait']);
            }
        }

        return $return;
    }

}

?>
