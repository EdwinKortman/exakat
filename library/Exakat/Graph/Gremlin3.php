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

namespace Exakat\Graph;

use Exakat\Graph\Graph;
use Exakat\Exceptions\UnableToReachGraphServer;
use Exakat\Exceptions\Neo4jException;
use Exakat\Exceptions\GremlinException;

class Gremlin3 extends Graph {
    const CHECKED = true;
    const UNCHECKED = false;
    
    private $scriptDir  = '';
    private $neo4j_host = '';
    private $neo4j_auth = '';
    
    private $status     = self::UNCHECKED;
    
    private $log        = null;
    
    public function __construct($config) {
        parent::__construct($config);
        
        $this->scriptDir = $config->neo4j_folder.'/scripts/';

        $this->neo4j_host   = $config->neo4j_host.':'.$config->neo4j_port;

        if ($this->config->neo4j_login !== '') {
            $this->neo4j_auth   = base64_encode($this->config->neo4j_login.':'.$this->config->neo4j_password);
        }
        
        if ($config->project != 'default' && $config->project != 'test' &&
            file_exists($config->projects_root.'/projects/'.$config->project)) {
            $this->log = fopen($config->projects_root.'/projects/'.$config->project.'/log/gremlin.log', 'a');
            fwrite($this->log, "New connexion \n");
        }
    }
    
    function __destruct() {
        if ($this->log !== null) {
            fwrite($this->log, "End connexion \n");
            fclose($this->log);
        }
    }
    
    private function checkConfiguration() {
        if (!file_exists($this->config->neo4j_folder)) {
            throw new Neo4jException("Error in the path to the Neo4j folder (".$this->config->neo4j_folder."). Please, check config/exakat.ini\n");
        }

        if (!file_exists($this->scriptDir)) {
            mkdir($this->scriptDir, 0755);
        } elseif (!is_writable($this->scriptDir)) {
            throw new Neo4jException("Can't write in '$this->scriptDir'. Exakat needs to write in this folder.\n");
        }
        
        $this->status = self::CHECKED;
    }

    public function query($query, $params = array(), $load = array()) {
        if ($this->status === self::UNCHECKED) {
            $this->checkConfiguration();
        }

        if (!is_array($load)) {
            $load = array($load);
        }
        
        $init = array();

        if (!empty($params)) {
            // Avoid changing arg10 to 'string'0 if query has more than 10 arguments.
            krsort($params);
            
            foreach($params as $name => $value) {
                if (is_string($value) && strlen($value) > 2000) {
                    $gremlin = "'''".str_replace('$', '\\$', $value)."'''";

                    // what about factorise this below? 
                    $defName = 'a'.crc32($gremlin);
                    $defFileName = $this->scriptDir.$defName.'.gremlin';

                    if (file_exists($defFileName)) {
                        $query = str_replace($name, $defName, $query);
                        $load[] = $defName;

                        unset($params[$name]);
                    } else {
                        $gremlin = 'def '.$defName.'() {'.$gremlin.'}';
                        file_put_contents($defFileName, $gremlin);

                        $query = str_replace($name, $defName, $query);
                        $load[] = $defName;

                        unset($params[$name]);
                    }
                } elseif (is_array($value)) {
                    $gremlin = $this->toMap($value);
                    $defName = 'a'.crc32($gremlin);
                    $defFileName = $this->scriptDir.$defName.'.gremlin';

                    if (file_exists($defFileName)) {
                        $query = str_replace($name, $defName, $query);
                        $init[] = $defName.' = '.$defName.'();';

                        $load[] = $defName;
                        unset($params[$name]);
                    } else {
                        $script = 'def '.$defName.'() '.$gremlin;

                        if (strlen($gremlin) > 1000000 ) {
                            $loader = <<<GREMLIN
def $defName() { 
    x = [];
    new File("$this->scriptDir/$defName.txt").each({ line -> x.push(line)});
    x; 
}
GREMLIN;
                            file_put_contents($defFileName, $loader);
//                            file_put_contents($this->scriptDir.$defName.'.txt', implode("\n", array_map(function ($x) { return addslashes($x); }, (array) $value)));
                            file_put_contents($this->scriptDir.$defName.'.txt', implode("\n", $value));
                        } elseif (strlen($gremlin) > 65535 ) {
                            $loader = <<<GREMLIN
def $defName() { 
    Eval.me(new File("$this->scriptDir/$defName.txt").getText());
}
GREMLIN;
                            file_put_contents($defFileName, $loader);
                            file_put_contents($this->scriptDir.$defName.'.txt', $this->toMap($value));
                        } else {
                            file_put_contents($defFileName, 'def '.$defName.'() { '.$this->toMap($value) .'}');
                        }

                        $query = str_replace($name, $defName, $query);
                        $init[] = $defName.' = '.$defName.'();';

                        $load[] = $defName;
                        unset($params[$name]);
                    }
                } else { // a short string (less than 2000) : hardcoded
                    $query = str_replace($name, "'''".addslashes($value)."'''", $query);
                    unset($params[$name]);
                }
            }
        }
        
        $query = implode("\n", $init)."\n".$query;

        $getString = 'script='.urlencode($query);

        if (count($load) == 1) {
            $getString .= '&load='.urlencode(array_pop($load));
        } elseif (count($load) > 1) {
            $getString .= '&load='.implode(',', array_map('urlencode', $load));
        } // else (aka 0) is ignored (nothing to do)
    
        assert(strlen($getString) < 20000,  
              'Query string too big for GET ('. strlen($getString). ")\n" . 'Query : ' . $query . "\n\n" . print_r($params, true));
        if (strlen($getString) > 20000) {
            return array();
        }

        $ch = curl_init();

        //set the url, number of POST vars, POST data
        $headers = array( 'User-Agent: exakat',
                          'X-Stream: true');
        if (!empty($this->neo4j_auth)) {
            $headers[] = 'Authorization: Basic '.$this->neo4j_auth;
        }
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch,CURLOPT_URL,            'http://'.$this->neo4j_host.'/tp/gremlin/execute?'.$getString);
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST,  'GET');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_IPRESOLVE,      CURL_IPRESOLVE_V4);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);
    
        $result = json_decode($result);
        if (isset($result->errormessage)) {
            if ($this->log !== null) {
                fwrite($this->log, $query."\n".$result->errormessage."\n");
            }
            throw new GremlinException($result->errormessage, $query);
        }

        return $result;
    }

    public function queryOne($query, $params = array(), $load = array()) {
        if ($this->status === self::UNCHECKED) {
            $this->checkConfiguration();
        }

        $res = $this->query($query, $params, $load);
        if (!($res instanceof \Stdclass) || !isset($res->results)) {
            throw new GremlinException('Server is not responding');
        }
        
        if (is_array($res->results)) {
            return $res->results[0];
        } else {
            return $res->results;
        }
    }

    public function serverInfo() {
        if ($this->status === self::UNCHECKED) {
            $this->checkConfiguration();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://'.$this->config->neo4j_host);
        curl_setopt($ch, CURLOPT_PORT, $this->config->neo4j_port);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
    
    private function toMap($array) {
        if (empty($array)) {
            return "[  ]";
        }
        $keys = array_keys($array);
        $key = $keys[0];
        if (is_array($array[$key])) {
            $map = array();
            foreach($array as $key => $value) {
                $a = array_map(function ($x) { return addslashes($x); }, $value);
                $map[] = "'''".addslashes($key)."''':['''".implode("''','''", $a)."''']";
            }
            $map = "[" . implode(', ', $map). "]"; 
        } elseif (is_object($array[$key])) {
            $map = array();
            foreach($array as $key => $value) {
                $a = array_map(function ($x) { return addslashes($x); }, (array) $value);
                $map[] = "'''".addslashes($key)."''':['''".implode("''','''", $a)."''']";
            }
            $map = "[" . implode(', ', $map). "]"; 
        } else {
            $array = array_map(function ($x) { return addslashes($x); }, $array);
            $map = "['''".implode("''','''", $array)."''']";
        }
        
        return $map;
    }
}

?>
