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

        if ($this->config->project != 'default') {
            if (file_exists($this->config->projects_root.'/projects/'.$this->config->project.'/report/')) {
                display('Cleaning the project first');
                $clean = new Clean($this->gremlin, $this->config);
                $clean->run();
            }

            display('Adding project '.$this->config->project.' to the queue');
            $queuePipe = fopen($this->pipefile, 'w');
            fwrite($queuePipe, $this->config->project."\n");
            fclose($queuePipe);
        } elseif (!empty($this->config->filename)) {
            if (!file_exists($this->config->projects_root.'/projects/onepage/code/'.$this->config->filename.'.php')) {
                throw new NoSuchFile('No such file "'.$this->config->filename.'" in /in/ folder');
            }

            if (file_exists($this->config->projects_root.'/projects/onepage/reports/'.$this->config->filename.'.json')) {
                throw new ReportAlreadyDone($this->config->filename);
            }

            display('Adding file '.$this->config->project.' to the queue');

            $queuePipe = fopen($this->pipefile, 'w');
            fwrite($queuePipe, $this->config->filename."\n");
            fclose($queuePipe);
        }

        display('Done');
    }
}

?>
