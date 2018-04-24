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


namespace Exakat\Tasks;

use Exakat\Config;
use Exakat\Tasks\Clean;
use Exakat\Tasks\Jobqueue;
use Exakat\Exceptions\NoJobqueueStarted;
use Exakat\Exceptions\NoSuchFile;
use Exakat\Exceptions\ReportAlreadyDone;

class Queue extends Tasks {
    const CONCURENCE = self::ANYTIME;

    private $pipefile = Jobqueue::PATH;

    public function run() {
        if (!file_exists($this->pipefile)) {
            throw new NoJobqueueStarted();
        }

        if ($this->config->stop === true) {
            display('Stopping queue');
            $queuePipe = fopen($this->pipefile, 'w');
            fwrite($queuePipe, "quit\n");
            fclose($queuePipe);

            return;
        }

        if ($this->config->ping === true) {
            display('Ping queue');
            $queuePipe = fopen($this->pipefile, 'w');
            fwrite($queuePipe, "ping\n");
            fclose($queuePipe);

            return;
        }

        if ($this->config->project !== 'default' && !empty($this->config->repository)) {
            display('Init project '.$this->config->project.' to with '.$this->config->repository);
            $queuePipe = fopen($this->pipefile, 'w');
            if (is_resource($queuePipe)) {
                fwrite($queuePipe, 'init '.$this->config->project.' '.$this->config->repository.PHP_EOL);
                fclose($queuePipe);
            } else {
                print "Couldn't write to queue\n";
            }
        } elseif ($this->config->project !== 'default' && $this->config->format !== null) {
            display('Report project '.$this->config->project.' to with format '.$this->config->format);
            $queuePipe = fopen($this->pipefile, 'w');
            if (is_resource($queuePipe)) {
                fwrite($queuePipe, 'report '.$this->config->project.' '.$this->config->format.PHP_EOL);
                fclose($queuePipe);
            } else {
                print "Couldn't write to queue\n";
            }
        } elseif ($this->config->project !== 'default') {
            if (file_exists($this->config->projects_root.'/projects/'.$this->config->project.'/report/')) {
                display('Cleaning the project first');
                $clean = new Clean($this->gremlin, $this->config);
                $clean->run();
            }

            display('Adding project '.$this->config->project.' to the queue');
            $queuePipe = fopen($this->pipefile, 'w');
            if (is_resource($queuePipe)) {
                fwrite($queuePipe, 'project '.$this->config->project.PHP_EOL);
                fclose($queuePipe);
            } else {
                print "Couldn't write to queue\n";
            }
        } elseif (!empty($this->config->filename)) {
            if (!file_exists($this->config->projects_root.'/projects/onepage/code/'.$this->config->filename.'.php')) {
                throw new NoSuchFile('No such file "'.$this->config->filename.'" in /in/ folder');
            }

            if (file_exists($this->config->projects_root.'/projects/onepage/reports/'.$this->config->filename.'.json')) {
                throw new ReportAlreadyDone($this->config->filename);
            }

            display('Adding file '.$this->config->project.' to the queue');

            $queuePipe = fopen($this->pipefile, 'w');
            if (is_resource($queuePipe)) {
                fwrite($queuePipe, 'onepage '.$this->config->filename.PHP_EOL);
                fclose($queuePipe);
            } else {
                print "Couldn't write to queue\n";
            }
            
        }

        display('Done');
    }
}

?>
