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

namespace Exakat;

use Exakat\Tasks;
use Exakat\Config;

class Exakat {
    const VERSION = '0.8.8';
    const BUILD = 459;
    
    private $gremlin = null;
    
    public function __construct($gremlin) {
        $this->gremlin = $gremlin;
    }
    
    public function execute(Config $config) {
        switch ($config->command) {
            case 'doctor' : 
                $doctor = new Tasks\Doctor($this->gremlin);
                $doctor->run($config);
                break;

            case 'init' : 
                $task = new Tasks\Initproject($this->gremlin);
                $task->run($config);
                break;

            case 'anonymize' : 
                $task = new Tasks\Anonymize($this->gremlin);
                $task->run($config);
                break;

            case 'files' : 
                $task = new Tasks\Files($this->gremlin);
                $task->run($config);
                break;

            case 'load' : 
                $task = new Tasks\Load($this->gremlin);
                $task->run($config);
                break;

            case 'stat' : 
                $task = new Tasks\Stat($this->gremlin);
                $task->run($config);
                break;

            case 'analyze' : 
                $task = new Tasks\Analyze($this->gremlin);
                $task->run($config);
                break;

            case 'results' : 
                $task = new Tasks\Results($this->gremlin);
                $task->run($config);
                break;

            case 'export' : 
                $task = new Tasks\Export($this->gremlin);
                $task->run($config);
                break;

            case 'report' : 
                $task = new Tasks\Report2($this->gremlin);
                $task->run($config);
                break;

            case 'project' : 
                $task = new Tasks\Project($this->gremlin);
                $task->run($config);
                break;

            case 'magicnumber' : 
                $task = new Tasks\Magicnumber($this->gremlin);
                $task->run($config);
                break;

            case 'clean' : 
                $task = new Tasks\Clean($this->gremlin);
                $task->run($config);
                break;

            case 'status' : 
                $task = new Tasks\Status($this->gremlin);
                $task->run($config);
                break;

            case 'help' : 
                $task = new Tasks\Help($this->gremlin);
                $task->run($config);
                break;

            case 'cleandb' : 
                $task = new Tasks\CleanDb($this->gremlin);
                $task->run($config);
                break;

            case 'onepage' : 
                $task = new Tasks\OnePage($this->gremlin);
                $task->run($config);
                break;

            case 'update' : 
                $task = new Tasks\Update($this->gremlin);
                $task->run($config);
                break;

            case 'onepagereport' : 
                $task = new Tasks\OnepageReport($this->gremlin);
                $task->run($config);
                break;

            case 'phploc' : 
                $task = new Tasks\Phploc($this->gremlin);
                $task->run($config);
                break;

            case 'findextlib' : 
                $task = new Tasks\FindExternalLibraries($this->gremlin);
                $task->run($config);
                break;

            case 'dump' : 
                $task = new Tasks\Dump($this->gremlin);
                $task->run($config);
                break;

            case 'jobqueue' : 
                $task = new Tasks\Jobqueue($this->gremlin);
                $task->run($config);
                break;

            case 'queue' : 
                $task = new Tasks\Queue($this->gremlin);
                $task->run($config);
                break;

            case 'test' : 
                $task = new Tasks\Test($this->gremlin);
                $task->run($config);
                break;

            case 'remove' : 
                $task = new Tasks\Remove($this->gremlin);
                $task->run($config);
                break;

            case 'server' : 
                $task = new Tasks\Server($this->gremlin);
                $task->run($config);
                break;

            case 'upgrade' : 
                $task = new Tasks\Upgrade($this->gremlin);
                $task->run($config);
                break;

            case 'version' : 
            default : 
                $version = self::VERSION;
                $build = self::BUILD;
                $date = date('r', filemtime(__FILE__));
                echo "
 ________                 __              _    
|_   __  |               [  |  _         / |_  
  | |_ \_| _   __  ,--.   | | / ]  ,--. `| |-' 
  |  _| _ [ \ [  ]`'_\ :  | '' <  `'_\ : | |   
 _| |__/ | > '  < // | |, | |`\ \ // | |,| |,  
|________|[__]`\_]\'-;__/[__|  \_]\'-;__/\__/  
                                               

Exakat : @ 2014-2016 Damien Seguy. 
Version : ", $version, ' - Build ', $build, ' - ', $date, "\n";

                break;
        }
    }
}

?>
