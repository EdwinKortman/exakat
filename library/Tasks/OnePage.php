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

class OnePage extends Tasks {
    private $project_dir = '.';
    private $config = null;
    
    const TOTAL_STEPS = 11;
    
    public function run(\Config $config) {
        $this->config = $config;
        
        $progress = 0;
        
        $begin = microtime(true);
        $path = $config->projects_root.'/projects/onepage/log';
        $this->project_dir = $config->projects_root.'/projects/onepage/';

        // checking for installation
        if (!file_exists($this->project_dir)) {
            shell_exec('php '.$config->executable.' init -p onepage ');
            mkdir($this->project_dir.'/code', 0755);
            shell_exec('php '.$config->executable.' phploc -p onepage ');
        }

        // todo : check that there is indeed this project or create it.
        if (!file_exists($config->filename)) {
            die("Can't find the file '$config->filename'. Aborting\n");
        }

        // todo : check that there is indeed this project or create it.
        if (!is_file($config->filename) || !is_readable($config->filename)) {
            die("'$config->filename' must be a readable file. Aborting\n");
        }

        $this->cleanLog($path);

        copy($config->filename, $config->projects_root.'/projects/onepage/code/onepage.php');

        $this->logTime('Start');

        $datastorePath = $config->projects_root.'/projects/onepage/datastore.sqlite';
        if (file_exists($datastorePath)) {
            unlink($datastorePath);
        }
        
        unset($this->datastore);
        $this->datastore = new \Datastore($config, \Datastore::CREATE);
        
        $audit_start = time();
        $this->datastore->addRow('hash', array('audit_start'    => $audit_start,
                                               'exakat_version' => \Exakat::VERSION,
                                               'exakat_build'   => \Exakat::BUILD,
                                               ));

        display("Cleaning DB\n");
        $task = new CleanDb();
        $task->run($config);

        $this->logTime('CleanDb');

        display("Running files\n");
        $task = new Files();
        $task->run($config);

        $this->logTime('Files');

        display("Running project 'onepage'\n");

        $task = new Load();
        $task->run($config);

        display("Project loaded\n");
        $this->logTime('Loading');

        $task = new Build_root();
        $task->run($config);

        display("Build root\n");
        $this->logTime('Build_root');

        $task = new Tokenizer();
        $task->run($config);

        $this->logTime('Tokenizer');
        display("Project tokenized\n");

        try {
            $task = new Analyze();
            $task->run($config);
            
            rename($config->projects_root.'/projects/onepage/log/analyze.log', $config->projects_root.'/projects/onepage/log/analyze.onepage.log');
        } catch (\Exception $e) {
            echo "Error while running the Analyze $theme \n",
                 $e->getMessage();
            file_put_contents($config->projects_root.'/projects/onepage/log/analyze.'.$themeForFile.'.final.log', $e->getMessage());
            die();
        }

        display("Project analyzed\n");
        $this->logTime('Analyze');

        $b1 = microtime(true);
        $task = new Dump();
        $task->run($config);
        display("Project dumped\n");
        $e1 = microtime(true);
        print "Dump + Report : ".number_format(($e1 - $b1) * 1000, 2)." ms\n";

        $b1 = microtime(true);
        $task = new Report2();
        $task->run($config);
        display("Project reported\n");
        $this->logTime('Report');
        $e1 = microtime(true);
        print "Dump + Report : ".number_format(($e1 - $b1) * 1000, 2)." ms\n";

        $b1 = microtime(true);
        $task = new EmptyTask();
        $task->run($config);
        display("Empty task\n");
        $this->logTime('Report');
        $e1 = microtime(true);
        print "Dump + Report : ".number_format(($e1 - $b1) * 1000, 2)." ms\n";

        display("Project reported\n");

        unlink($config->projects_root.'/projects/onepage/code/onepage.php');

        $audit_end = time();
        $this->datastore->addRow('hash', array('audit_end'    => $audit_end,
                                               'audit_length' => $audit_end - $audit_start));

        $this->logTime('Final');
        display("End 2\n");
        $end = microtime(true);
        print('Total time : '.number_format($end - $begin, 2)."s\n");
        
        $this->logTime('Files');
    }

    private function logTime($step) {
        static $log, $begin, $end, $start;

        if ($log === null) {
            $log = fopen($this->project_dir.'/log/project.timing.csv', 'w+');
            fwrite($log, "Yes $step\n");
        }
        $end = microtime(true);
        if ($begin === null) {
            $begin = $end;
            $start = $end;
        }

        fwrite($log, $step."\t".($end - $begin)."\t".($end - $start)."\n");
        $begin = $end;
    }
}

?>
