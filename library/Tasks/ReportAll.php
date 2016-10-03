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


namespace Tasks;

class ReportAll extends Tasks {
    public function run(\Exakat\Config $config) {
        display("Starting reportAll\n");

        $formats = array('Markdown', 'Sqlite', 'Devoops', 'Html', 'Text');
        $reportType = 'Premier';
        $oldConfig = \Exakat\Config::factory();
        
        foreach($formats as $format) {
            display("Reporting $format\n");
            $args = array ( 1 => 'report',
                            2 => '-p',
                            3 => $config->project,
                            4 => '-f',
                            5 => 'report',
                            6 => '-format',
                            7 => $format,
                            8 => '-report',
                            9 => $reportType,
                            );
            $config = \Exakat\Config::factory($args);
            
            $report = new Report();
            $report->run($config);
            unset($report);
        }
        \Exakat\Config::factory($oldConfig);

        // generating counts
        display("Reporting Counts (Sqlite)\n");
        $args = array ( 1 => 'report',
                        2 => '-p',
                        3 => $config->project,
                        4 => '-f',
                        5 => 'counts',
                        6 => '-format',
                        7 => 'Sqlite',
                        8 => '-report',
                        9 => 'Counts',
                        );
        
        $report = new Report();
        $report->run(\Exakat\Config::factory($args));
        unset($report);

        display("Done\n");
    }
}

?>
