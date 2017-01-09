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


namespace Exakat\Reports;

use Exakat\Analyzer\Analyzer;

class PhpConfiguration extends Reports {
    const FILE_EXTENSION = 'txt';
    const FILE_FILENAME  = 'compilation';

    public function __construct() {
        parent::__construct();
    }
    
    public function generateFileReport($report) {
        return false;
    }

    public function generate($folder, $name = null) {
        $themed = Analyzer::getThemeAnalyzers('Appinfo');
        $res = $this->sqlite->query('SELECT analyzer, count FROM resultsCounts WHERE analyzer IN ("'.implode('", "', $themed).'")');
        $sources = array();
        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $sources[$row['analyzer']] = $row['count'];
        }
        
        $configureDirectives = json_decode(file_get_contents($this->config->dir_root.'/data/configure.json'));
        
        // preparing the list of PHP extensions to compile PHP with
        $return = array(<<<TEXT
;;;;;;;;;;;;;;;;;;;;;;;;
; PHP configure list   ;
;;;;;;;;;;;;;;;;;;;;;;;;

TEXT
,
'./configure');
        $pecl = array();
        foreach($configureDirectives as $ext => $configure) {
            if (isset($sources[$configure->analysis])) {
                if(!empty($configure->activate) && $sources[$configure->analysis] != 0) {
                    $return[] = ' '.$configure->activate;
                    if (!empty($configure->others)) {
                        $return[] = "   ".join("\n    ", $configure->others);
                    }
                    if (!empty($configure->pecl)) {
                        $pecl[] = '#pecl install '.basename($configure->pecl).' ('.$configure->pecl.')';
                    }
                } elseif(!empty($configure->deactivate) && $sources[$configure->analysis] == 0) {
                    $return[] = ' '.$configure->deactivate;
                } 
            }
        }
        
        $return = array_merge($return, array(
                   '',
                   '; For debug purposes',
                   ';--enable-dtrace',
                   ';--disable-phpdbg',
                   '',
                   ';--enable-zend-signals',
                   ';--disable-opcache',
            ));
        
        $final = '';
        if (!empty($pecl)) {
            $c = count($pecl);
            $final .= "# install ".( $c === 1 ? 'one' : $c)." extra extension".($c === 1 ? '' : 's')."\n";
            $final .= implode("\n", $pecl)."\n\n";
        }
        $final .= implode("\n", $return);

        $shouldDisableFunctions = json_decode(file_get_contents($this->config->dir_root.'/data/shouldDisableFunction.json'));
        $functionsList = array();
        $classesList = array();
        foreach((array) $shouldDisableFunctions as $ext => $toDisable) {
            if ($sources[$ext] == 0) {
                if (isset($toDisable->functions)) { 
                    $functionsList[] = $toDisable->functions;
                }
                if (isset($toDisable->classes)) { 
                    $classesList[] = $toDisable->classes;
                }
            }
        }
        
        if (empty($functionsList)) {
            $functionsList = '';
        } else {
            $functionsList = call_user_func_array('array_merge', $functionsList);
            $functionsList = join(',', $functionsList);
        }
        if (empty($classesList)) {
            $classesList = '';
        } else {
            $classesList = call_user_func_array('array_merge', $classesList);
            $classesList = join(',', $classesList);
        }

        // preparing the list of PHP directives to review before using this application
        $directives = array('standard', 'bcmath', 'date', 'file', 
                            'fileupload', 'mail', 'ob', 'env',
                            // standard extensions
                            'apc', 'amqp', 'apache', 'assertion', 'curl', 'dba',
                            'filter', 'image', 'intl', 'ldap',
                            'mbstring', 
                            'opcache', 'openssl', 'pcre', 'pdo', 'pgsql',
                            'session', 'sqlite', 'sqlite3', 
                            // pecl extensions
                            'com', 'eaccelerator',
                            'geoip', 'ibase', 
                            'imagick', 'mailparse', 'mongo', 
                            'trader', 'wincache', 'xcache'
                             );

        $data = array();
        $res = $this->sqlite->query(<<<SQL
SELECT analyzer FROM resultsCounts 
    WHERE ( analyzer LIKE "Extensions/Ext%" OR 
            analyzer IN ("Structures/FileUploadUsage", "Php/UsesEnv"))
        AND count > 0
SQL
);
        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            if ($row['analyzer'] == 'Structures/FileUploadUsage') {
                $data['File Upload'] = (array) json_decode(file_get_contents($this->config->dir_root.'/data/directives/fileupload.json'));
            } elseif ($row['analyzer'] == 'Php/UsesEnv') {
                $data['Environnement'] = (array) json_decode(file_get_contents($this->config->dir_root.'/data/directives/env.json'));
            } else {
                $ext = substr($row['analyzer'], 14);
                if (in_array($ext, $directives)) {
                    $data[$ext] = (array) json_decode(file_get_contents($this->config->dir_root.'/data/directives/'.$ext.'.json'));
                }
            }
        }
        
        $directives = <<<TEXT

;;;;;;;;;;;;;;;;;;;;;;;;;;
; Suggestion for php.ini ;
;;;;;;;;;;;;;;;;;;;;;;;;;;

; The directives below are selected based on the code provided. 
; They only cover the related directives that may have an impact on the code
;
; The list may not be exhaustive
; The suggested values are not recommendations, and should be reviewed and adapted
;



TEXT;
        foreach($data as $section => $details) {
            $directives .= "[$section]\n";
            
            foreach((array) $details as $detail) {
                if ($detail->name == 'Extra configurations') {
                    preg_match('#(https?://[^"]+?)"#is', $detail->documentation, $url);
                    $directives .= "; More information about $section : 
;$url[1]

";
                } else {
                    $documentation = wordwrap(' '.$detail->documentation, 80, "\n; ");
                    $directives .= ";$documentation
$detail->name = $detail->suggested

";
                }
            }
            
            if ($section === 'standard') {
                    $directives .= ";$documentation
disable_functions = $functionsList
disable_classes = $classesList

";
            }

            $directives .= "\n\n";
        }
        
        $final .= "\n\n".$directives;
        
        if ($name === null) {
            return $final ;
        } else {
            file_put_contents($folder.'/'.$name.'.'.self::FILE_EXTENSION, $final);
            return true;
        }
    } 
} 

?>