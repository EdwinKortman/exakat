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

class FindExternalLibraries extends Tasks {
    const WHOLE_DIR    = 1;
    const FILE_ONLY    = 2;
    const PARENT_DIR   = 3; // Whole_dir and parent.
    const COMPOSER_DIR = 4; // whole_dir + 4 levels (ex : fzaninoto/faker/src/Faker/Factory.php)

    // classic must be in lower case form.
    private $classic = array('adoconnection'    => self::WHOLE_DIR,
                             'bbq'              => self::WHOLE_DIR,
                             'cpdf'             => self::WHOLE_DIR, // ezpdf
                             'cakeplugin'       => self::PARENT_DIR, // cakephp
                             'dompdf'           => self::PARENT_DIR,
                             'fpdf'             => self::FILE_ONLY,
                             'faker\\factory'   => self::COMPOSER_DIR,
                             'graph'            => self::PARENT_DIR, // Jpgraph
                             'html2pdf'         => self::WHOLE_DIR, // contains tcpdf
                             'htmlpurifier'     => self::WHOLE_DIR,
                             'http_class'       => self::WHOLE_DIR,
                             'idna_convert'     => self::WHOLE_DIR,
                             'lessc'            => self::FILE_ONLY,
                             'magpierss'        => self::WHOLE_DIR,
                             'markdown_parser'  => self::FILE_ONLY,
                             'markdown'         => self::WHOLE_DIR,
                             'mpdf'             => self::WHOLE_DIR,
                             'oauthtoken'       => self::WHOLE_DIR,
                             'passwordhash'     => self::FILE_ONLY,
                             'pchart'           => self::WHOLE_DIR,
                             'pclzip'           => self::FILE_ONLY,
                             'gacl'             => self::WHOLE_DIR,
                             'propel'           => self::PARENT_DIR,
                             'gettext_reader'   => self::WHOLE_DIR,
                             'phpexcel'         => self::WHOLE_DIR,
                             'phpmailer'        => self::WHOLE_DIR,
                             'qrcode'           => self::FILE_ONLY,
                             'services_json'    => self::FILE_ONLY,
                             'sfyaml'           => self::WHOLE_DIR,
                             'swift'            => self::WHOLE_DIR,
                             'smarty'           => self::WHOLE_DIR,
                             'tcpdf'            => self::WHOLE_DIR,
                             'text_diff'        => self::WHOLE_DIR,
                             'text_highlighter' => self::WHOLE_DIR,
                             'tfpdf'            => self::WHOLE_DIR,
                             'utf8'             => self::WHOLE_DIR,
                             'ci_xmlrpc'        => self::FILE_ONLY,
                             'xajax'            => self::PARENT_DIR,
                             'yii'              => self::FILE_ONLY,
                             );

    public function run(\Config $config) {
        $project = $config->project;
        if ($project == 'default') {
            die("findextlib needs a -p <project>\nAborting\n");
        }

        if (!file_exists($config->projects_root.'/projects/'.$project.'/')) {
            die("No such project as $project.\nAborting\n");
        }

        $dir = $config->projects_root.'/projects/'.$project.'/code';
        $configFile = $config->projects_root.'/projects/'.$project.'/config.ini';
        $ini = parse_ini_file($configFile);
        
        if ($config->update && isset($ini['FindExternalLibraries'])) {
            display('Not updating '.$project.'/config.ini. This tool was already run. Please, clean the config.ini file in the project directory, before running it again.');
            return; //Cancel task
        }
    
        $files = $this->datastore->getCol('files', 'file');
        display('Processing '.count($files).' files');
        if (empty($files)) {
            display('No files to process. Aborting');
            return;
        }
        
        $r = array();
        foreach($files as $file) {
            $s = $this->process($file);
            
            if (!empty($s)) {
                print_r($s);
                $r[] = $s;
            }
       }

       if (!empty($r)) {
           $newConfigs = call_user_func_array('array_merge', $r);
        } else {
            $newConfigs = array();
        }

        if (count($newConfigs) == 1) {
            display('One external library is going to be omitted : '.join(', ', array_keys($newConfigs)));
        } elseif (count($newConfigs)) {
            display(count($newConfigs).' external libraries are going to be omitted : '.join(', ', array_keys($newConfigs)));
        }

        $store = [];
        foreach($newConfigs as $library => $file) {
            $store[] = ['library' => $library,
                        'file'    => $file];
        }

        $this->datastore->cleanTable('externallibraries');
        $this->datastore->addRow('externallibraries', $store);

        if ($config->update === true && count($newConfigs) > 0) {
             display('Updating '.$project.'/config.ini');
             $ini = file_get_contents($configFile);
             $ini = preg_replace("#(ignore_dirs\[\] = \/.*?\n)\n#is", '$1'."\n".';Ignoring external libraries'."\n".'ignore_dirs[] = '.join("\n".'ignore_dirs[] = ', $newConfigs)."\n;Ignoring external libraries\n\n", $ini);

             $ini .= "\nFindExternalLibraries = 1\n";

             file_put_contents($configFile, $ini);
        } else {
            display('Not updating '.$project.'/config.ini. '.count($newConfigs).' external libraries found');
        }
    }
    
    private function process($filename) {
        $return = array();

        $php = new \Phpexec();
        static $t_class, $t_namespace, $t_whitecode;
        if (!isset($t_class)) {
            $php->getTokens();
            $t_class = $php->getTokenValue('T_CLASS');
            $t_namespace = $php->getTokenValue('T_NAMESPACE');
            $t_whitecode = $php->getWhiteCode();
        }
        
        $tokens = $php->getTokenFromFile($filename);
        if (count($tokens) == 1) {
            return $return;
        }
        $this->log->log("$filename : ".count($tokens));
        print $filename."\n";
        $namespace = '';

        foreach($tokens as $id => $token) {
            if (is_string($token)) { continue; }

            if (in_array($token[0], $t_whitecode))  { continue; }

            if ($token[0] == $t_namespace) {
                if (!is_array($tokens[$id + 2])) { continue; }

                // This will only work with one-string namespaces. Might need to upgrade this later to full NSname
                $namespace = strtolower($tokens[$id + 2][1]);
                if (!is_string($namespace)) {
                    // ignoring errors in the parsed code. Should go to log.
                    continue;
                }
                continue;
            }
        
            if ($token[0] == $t_class) {
                if (!is_array($tokens[$id + 2])) { continue; }
                $class = $tokens[$id + 2][1];
                if (!is_string($class)) {
                    // ignoring errors in the parsed code. Should go to log.
                    continue;
                }

                $lclass = strtolower($class);

                if (isset($this->classic[$lclass])) {
                    if ($this->classic[$lclass] == self::WHOLE_DIR) {
                        $returnPath = dirname(preg_replace('#.*projects/.*?/code/#', '/', $filename));
                    } elseif ($this->classic[$lclass] == self::PARENT_DIR) {
                        $returnPath = dirname(dirname(preg_replace('#.*projects/.*?/code/#', '/', $filename)));
                    } elseif ($this->classic[$lclass] == self::FILE_ONLY) {
                        $returnPath = preg_replace('#.*projects/.*?/code/#', '/', $filename);
                    }
                } elseif (isset($this->classic["$namespace\\$lclass"])) {
                    if ($this->classic[$namespace.'\\'.$lclass] == self::COMPOSER_DIR) {
                        $returnPath = dirname(dirname(dirname(dirname(preg_replace('#.*projects/.*?/code/#', '/', $filename)))));
                    }
                }

                if ($returnPath != '/') {
                    $return[$class] = $returnPath;
                }
            }
        }
    
        return $return;
    }
}

?>
