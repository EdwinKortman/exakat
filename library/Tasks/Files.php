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

class Files extends Tasks {
      static public $exts = array('php'      => array('php', 'php3', 'inc', 'tpl', 'phtml', 'tmpl', 'phps', 'ctp'  ),
                                  'images'   => array('jpg', 'gif', 'ico', 'png', 'svg', 'eps', 'psd', 'dot', 'dhp', 'JPG',),
                                  'media'    => array('ttf', 'swf', 'woff', 'eot', 'otf', ),
                                  'text'     => array('xml', 'txt', 'rst', 'md', 'markdown', 'po', 'mo', 'pot', 'dtd', 'TXT',
                                                      'WEBHELP', 'mxml', 'mime', 'latte', 'MIT', 'python', 'text'),
                                  'config'   => array('neon', 'ini', 'yml', 'yaml') ,
                                  'web'      => array('html', 'htm', 'css', 'js', 'json', 'less', 'webloc', 'wsdl',  ),
                                  'document' => array('doc', 'xls', 'docx', 'pdf', 'odt', 'epub', 'book', 'xlsx', 'ods', 'slk' ),
                                  'archives' => array('tgz', 'bz2' ,'z', 'zip', 'gz', 'tar', 'bz', 'tbz', ),
                                  'audio'    => array('mp3', 'fla', 'wav', 'xap', 'ses'),
                                  'video'    => array('avi', 'pxm') ,
                                  'data'     => array('sql', 'properties', 'yml', 'dist', 'csv', 'log', 'profile', 'info', 'module','install',
                                                      'sqlite', 'lang', 'conf', 'config', 'db', 'phar', 'db3', 'neon', 'data', 'ast'),
                                  'prog'     => array('py', 'bat', 'c', 'h', 'twig', 'sh', 'jar', 'java', 'rb', 'phpt', 'sass', 'scss',
                                                      'xsl', 'as', 'cmd','m4', 'dsp', 'sln', 'vcproj', 'w32', 'diff', 'pl', 'dsw', 'am', 'in', 'ac', ),
                                  'misc'     => array('test', 'table', 'dat', 'admin', 'cur', 'git', 'rng', 'bin',  'ser', 'mgc',),
                                  'security' => array('pub', 'pem', 'crt', 'xcf', ),
                     );

    public function run(\Config $config) {
        $dir = $config->project;

        $stats = array('notCompilable52' => 'N/C',
                       'notCompilable53' => 'N/C',
                       'notCompilable54' => 'N/C',
                       'notCompilable55' => 'N/C',
                       'notCompilable56' => 'N/C',
                       'notCompilable70' => 'N/C') ;
        $unknown = array();

        if ($config->project === null) {
            die("Usage : exakat files -p project\nAborting\n");
        } elseif (!file_exists($config->projects_root.'/projects/'.$dir)) {
            die("No such project as '{$config->projects_root}/projects/$dir'\nAborting\n");
        } elseif (!file_exists($config->projects_root.'/projects/'.$dir.'/code/')) {
            die("No code in project '$dir'\nAborting\n");
        }

        $ignoreDirs = array();
        foreach($config->ignore_dirs as $ignore) {
            if ($ignore[0] == '/') {
                $d = $config->projects_root.'/projects/'.$dir.'/code'.$ignore;
                if (!file_exists($d)) {
                    continue;
                }
                $d .= '*';
                $ignoreDirs[] = $d;
            } else {
                $ignoreDirs[] = '*'.$ignore.'*';
            }
        }

        display('Ignoring files');
        // Ignored files
        $this->datastore->cleanTable('ignoredFiles');
        $shellBase = 'find '.$config->projects_root.'/projects/'.$dir.'/code \\( -name "*.'.(join('" -o -name "*.', static::$exts['php'])).'" \\) \\( -path "'.(join('" -or -path "', $ignoreDirs )).'" \\) -type f -print0 | xargs -0 grep -H -c "^<?xml" | grep 0$ | cut -d\':\' -f1  ';
        $files = trim(shell_exec($shellBase));

        $files = preg_replace('#'.$config->projects_root.'/projects/.*?/code#is', '', $files);
        $files = explode("\n", $files);
        $files = array_map(function ($a) {
            return array('file' => $a);
        }, $files);

        $this->datastore->addRow('ignoredFiles', $files);

        // actually used files
        $this->datastore->cleanTable('files');
        $shellBase = 'find '.$config->projects_root.'/projects/'.$dir.'/code \\( -name "*.'.(join('" -o -name "*.', static::$exts['php'])).'" \\) \\( -not -path "'.(join('" -and -not -path "', $ignoreDirs )).'" \\) -type f -print0 | xargs -0 grep -H -c "^<?xml" | grep 0$ | cut -d\':\' -f1  ';

        $files = trim(shell_exec($shellBase));
        $files = preg_replace('#'.$config->projects_root.'/projects/.*?/code#is', '', $files);
        $files = explode("\n", $files);
        $files = array_map(function ($a) {
            return array('file' => $a);
        }, $files);

        $this->datastore->addRow('files', $files);

        $ignoreDirs = array();
        $ignoreName = array();
        foreach($config->ignore_dirs as $ignore) {
            if ($ignore[0] == '/') {
                $d = $config->projects_root.'/projects/'.$dir.'/code'.$ignore;
                if (file_exists($d)) {
                    $ignoreDirs[] = substr($ignore, 1);
                }
            } else {
                $ignoreName[] = $ignore;
            }
        }

        display('Counting files');
        $counting = new Phploc();
        $counting->run($config);
        display('Counted files');

        $versions = $config->other_php_versions;

        foreach($versions as $version) {
            display('Check compilation for '.$version);
            $stats['notCompilable'.$version] = -1;
            
            $shell = $shellBase . '  | tr "\n" "\0" |  xargs -n1 -P5 -0I {} sh -c "'.$config->{'php'.$version}.' -l {} 2>&1" || true';
            $res = trim(shell_exec($shell));

            $resFiles = explode("\n", $res);
            $incompilables = array();
    
            foreach($resFiles as $resFile) {
                if (substr($resFile, 0, 28) == 'No syntax errors detected in') {
                    continue;
                    // do nothing. All is fine.
                } elseif ($resFile == '') {
                    continue;
                    // do nothing. All is fine.
                } elseif (substr($resFile, 0, 17) == 'PHP Parse error: ') {
                    preg_match('#Parse error: (.+?) in (.+?) on line (\d+)#', $resFile, $r);
                    $incompilables[] = array('error' => $r[1], 'file' => str_replace($config->projects_root.'/projects/'.$dir.'/code/', '', $r[2]), 'line' => $r[3]);
                } elseif (substr($resFile, 0, 13) == 'Parse error: ') {
                    // Actually, almost a repeat of the previous. We just ignore it. (Except in PHP 5.4)
                    if ($version == '52') {
                        preg_match('#Parse error: (.+?) in (.+?) on line (\d+)#', $resFile, $r);
                        $incompilables[] = array('error' => $r[1], 'file' => str_replace($config->projects_root.'/projects/'.$dir.'/code/', '', $r[2]), 'line' => $r[3]);
                    }
                } elseif (substr($resFile, 0, 14) == 'PHP Warning:  ') {
                    preg_match('#PHP Warning:  (.+?) in (.+?) on line (\d+)#', $resFile, $r);
                    $incompilables[] = array('error' => $r[1], 'file' => str_replace($config->projects_root.'/projects/'.$dir.'/code/', '', $r[2]), 'line' => $r[3]);
                } elseif (substr($resFile, 0, 13) == 'Fatal error: ') {
                    preg_match('#Fatal error: (.+?) in (.+?) on line (\d+)#', $resFile, $r);
                    $incompilables[] = array('error' => $r[1], 'file' => str_replace($config->projects_root.'/projects/'.$dir.'/code/', '', $r[2]), 'line' => $r[3]);
                } elseif (substr($resFile, 0, 18) == 'PHP Fatal error:  ') {
                    // Actually, a repeat of the previous. We just ignore it.
                } elseif (substr($resFile, 0, 23) == 'PHP Strict standards:  ') {
                    preg_match('#PHP Strict standards:  (.+?) in (.+?) on line (\d+)#', $resFile, $r);
                    $incompilables[] = array('error' => $r[1], 'file' => str_replace($config->projects_root.'/projects/'.$dir.'/code/', '', $r[2]), 'line' => $r[3]);
                } elseif (substr($resFile, 0, 18) == 'Strict Standards: ') {
                    preg_match('#Strict Standards: (.+?) in (.+?) on line (\d+)#', $resFile, $r);
                    $incompilables[] = array('error' => $r[1], 'file' => str_replace($config->projects_root.'/projects/'.$dir.'/code/', '', $r[2]), 'line' => $r[3]);
                } elseif (substr($resFile, 0, 18) == 'Strict standards: ') {
                    preg_match('#Strict standards: (.+?) in (.+?) on line (\d+)#', $resFile, $r);
                    $incompilables[] = array('error' => $r[1], 'file' => str_replace($config->projects_root.'/projects/'.$dir.'/code/', '', $r[2]), 'line' => $r[3]);
                } elseif (substr($resFile, 0, 22) == 'PHP Strict Standards: ') {
                    preg_match('#PHP Strict Standards: (.+?) in (.+?) on line (\d+)#', $resFile, $r);
                    $incompilables[] = array('error' => $r[1], 'file' => str_replace($config->projects_root.'/projects/'.$dir.'/code/', '', $r[2]), 'line' => $r[3]);
                } elseif (substr($resFile, 0, 17) == 'PHP Deprecated:  ') {
                    preg_match('#PHP Deprecated:  (.+?) in (.+?) on line (\d+)#', $resFile, $r);
                    $incompilables[] = array('error' => $r[1], 'file' => str_replace($config->projects_root.'/projects/'.$dir.'/code/', '', $r[2]), 'line' => $r[3]);
                } elseif (substr($resFile, 0, 12) == 'Deprecated: ') {
                    preg_match('#Deprecated: (.+?) in (.+?) on line (\d+)#', $resFile, $r);
                    $incompilables[] = array('error' => $r[1], 'file' => str_replace($config->projects_root.'/projects/'.$dir.'/code/', '', $r[2]), 'line' => $r[3]);
                } elseif (substr($resFile, 0, 9) == 'Warning: ') {
                    preg_match('#Warning: (.+?) in (.+?) on line (\d+)#', $resFile, $r);
                    $incompilables[] = array('error' => $r[1], 'file' => str_replace($config->projects_root.'/projects/'.$dir.'/code/', '', $r[2]), 'line' => $r[3]);
                } elseif (substr($resFile, 0, 14) == 'Errors parsing') {
                    // ignore (stdout reporting)
                } else {
                    $this->log->log( "\nCouldn't interpret on syntax error : \n" .
                         print_r($resFile, true) .
                         print_r($res, true) .
                         "\n" . __FILE__ . "\n");
                    // Then, ignore it.
                }
            }
    
            $this->datastore->cleanTable('compilation'.$version);
            $this->datastore->addRow('compilation'.$version, $incompilables);
            $stats['notCompilable'.$version] = count($incompilables);
        }

        display('Check short tag (normal pass)');
        $stats['php'] = count($resFiles);
        $shell = $shellBase . ' | sort | tr "\n" "\0" |  xargs -n1 -P5 -0I '.$config->php.' -d short_open_tag=0 -r "echo count(token_get_all(file_get_contents(\$argv[1]))).\" \$argv[1]\n\";" 2>>/dev/null || true';
        
        $resultNosot = shell_exec($shell);
        $tokens = (int) array_sum(explode("\n", $resultNosot));

        display('Check short tag (with directive activated)');
        $shell = $shellBase . ' | sort |  tr "\n" "\0" |  xargs -n1 -P5 -0I '.$config->php.' -d short_open_tag=1 -r "echo count(token_get_all(file_get_contents(\$argv[1]))).\" \$argv[1]\n\";" 2>>/dev/null || true ';
        
        $resultSot = shell_exec($shell);
        $tokenssot = (int) array_sum(explode("\n", $resultSot));

        $this->datastore->cleanTable('shortopentag');
        if ($tokenssot != $tokens) {
            $nosot = explode("\n", trim($resultNosot));
            $nosot2 = array();
            foreach($nosot as $value) {
                list($count, $file) = explode(' ', $value);
                $nosot2[$file] = $count;
            }
            $nosot = $nosot2;
            unset($nosot2);

            $sot = explode("\n", trim($resultSot));
            $sot2 = array();
            foreach($sot as $value) {
                list($count, $file) = explode(' ', $value);
                $sot2[$file] = $count;
            }
            $sot = $sot2;
            unset($sot2);
    
            if (count($nosot) != count($sot)) {
                $this->log->log('Error in short open tag analyze : not the same number of files '.count($nosot).' / '.count($sot).".\n");
                display('Short tag KO');
                $shortOpenTag = array();
                foreach($nosot as $file => $countNoSot) {
                    if ($sot[$file] != $countNoSot) {
                        $shortOpenTag[] = array('file' => str_replace($config->projects_root.'/projects/'.$dir.'/code/', '', $file));
                    }
                }
            }
    
            $this->datastore->addRow('shortopentag', $shortOpenTag);
        } else {
            display('Short tag OK');
        }

        $this->datastore->addRow('hash', $stats);
        
        // composer.json
        display('Check composer');
        $composerInfo = array();
        $this->datastore->cleanTable('composer');
        if ($composerInfo['composer.json'] = file_exists($config->projects_root.'/projects/'.$dir.'/code/composer.json')) {
            $composerInfo['composer.lock'] = file_exists($config->projects_root.'/projects/'.$dir.'/code/composer.lock');
            
            $composer = json_decode(file_get_contents($config->projects_root.'/projects/'.$dir.'/code/composer.json'));
            
            if (isset($composer->autoload)) {
                $composerInfo['autoload'] = isset($composer->autoload->{'psr-0'}) ? 'psr-0' : 'psr-4';
            } else {
                $composerInfo['autoload'] = false;
            }
            
            if (isset($composer->require)) {
                $this->datastore->addRow('composer', (array) $composer->require);
            }
        }
        $this->datastore->addRow('hash', $composerInfo);
        
        display('Done');
        
        if ($config->json) {
            if ($unknown) {
                $stats['unknown'] = $unknown;
            }
            echo json_encode($stats);
        } else {
            display_r($stats);
            if ($unknown) {
                display_r($unknown);
            }
        }
    }
}

?>
