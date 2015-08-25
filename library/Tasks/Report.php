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

class Report implements Tasks {
    public function run(\Config $config) {
        $reportClass = "\\Report\\Report\\".$config->report;

        if (!class_exists($reportClass)) {
            die("Report '{$config->report}' doesn't exist.\nAborting\n");
        }

        if (!class_exists("\\Report\\Format\\".$config->format)) {
            die("Format '{$config->format}' doesn't exist. Choose among : ".implode(", ", \Report\Report::$formats)."\nAborting\n");
        }

        if (!file_exists($config->projects_root.'/projects/'.$config->project)) {
            die("Project '{$config->project} doesn't exist yet. Run init to create it.\nAborting\n");
        }

        if (!file_exists($config->projects_root.'/projects/'.$config->project.'/datastore.sqlite')) {
            die("Project hasn't been analyzed. Run project first.\nAborting\n");
        }

        $datastore = new \Datastore($config);
        \Analyzer\Analyzer::$datastore = $datastore;

        display( "Building report ".$config->report." for project ".$config->project." in file ".$config->file.", with format ".$config->format."\n");
        $begin = microtime(true);

        $report = new $reportClass($config->project);
        $report->prepare();
        $size = $report->render($config->format, $config->filename);

        $end = microtime(true);
        display( "Processing time : ".number_format($end - $begin, 2)." s\n");
        display( "Done\n");
    }
}

?>
