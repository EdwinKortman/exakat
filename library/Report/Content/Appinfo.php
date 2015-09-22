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


namespace Report\Content;

class Appinfo extends \Report\Content {
        // Which extension are being used ? 
       public $extensions = array(
                    'PHP' => array(
                            'Short tags'                 => 'Structures/ShortTags',
                            'Echo tags <?='              => 'Php/EchoTagUsage',
//                            'Closed scripts'  => 'Php/ClosedTags',
                            'Incompilable'               => 'Php/Incompilable',
                            
//                            'Iffectations'   => 'Structures/Iffectation',

                            '@ operator'                 => 'Structures/Noscream',
                            'Alternative syntax'         => 'Php/AlternativeSyntax',
                            'Magic constants'            => 'Constants/MagicConstantUsage',
                            'halt compiler'              => 'Php/Haltcompiler',
                            'Assertions'                 => 'Php/AssertionUsage',
          
                            'Casting'                    => 'Php/CastingUsage',
                            'Resources'                  => 'Structures/ResourcesUsage',
                            'Nested Loops'               => 'Structures/NestedLoops',
            
                            'Autoload'                   => 'Php/AutoloadUsage',
                            'inclusion'                  => 'Structures/IncludeUsage',
                            'include_once'               => 'Structures/OnceUsage',
                            'Output control'             => 'Extensions/Extob',
          
                            'Goto'                       => 'Php/Gotonames',
                            'Labels'                     => 'Php/Labelnames',

                            'Function dereferencing'     => 'Structures/FunctionSubscripting',
                            'Constant scalar expression' => 'Structures/ConstantScalarExpression',
                            '... usage'                  => 'Php/EllipsisUsage',

                            'File upload'                => 'Structures/FileUploadUsage',
                    ),

                    'Composer' => array(
                            'composer.json'              => 'Composer/UseComposer',
                            'composer autoload'          => 'Composer/Autoload',
                    ),

                    // filled later
                    'Composer Packages' => array(),

                    'Namespaces' => array(
                            'Namespaces'              => 'Namespaces/Namespacesnames',
//                            'Vendor'                  => 'Namespaces/Vendor',
                            'Alias'                   => 'Namespaces/Alias',
                    ),

                    'Variables' => array(
                            'References'              => 'Variables/References',
                            'Array'                   => 'Arrays/Arrayindex',
                            'Multidimensional arrays' => 'Arrays/Multidimensional',
                            'Array short syntax'      => 'Arrays/ArrayNSUsage',
                            'Variable variables'      => 'Variables/VariableVariables',

                            'PHP arrays'              => 'Arrays/Phparrayindex',

                            'Globals'                 => 'Php/SuperGlobalUsage',
                            'PHP SuperGlobals'        => 'Structures/GlobalUsage',
                    ),

                    'Functions' => array(
                            'Functions'                => 'Functions/Functionnames',
                            'Redeclared PHP Functions' => 'Functions/RedeclaredPhpFunction',
                            'Closures'             => 'Functions/Closures',

                            'Typehint'             => 'Functions/Typehints',
                            'Scalar Typehint'      => 'Php/ScalarTypehintUsage',
                            'Return Typehint'      => 'Php/ReturnTypehintUsage',
                            'Static variables'     => 'Variables/StaticVariables',

                            'Dynamic functioncall' => 'Functions/Dynamiccall',

                            'Recursive Functions'  => 'Functions/Recursive',
                            'Generator Functions'  => 'Functions/IsGenerator',
                            'Conditioned Function' => 'Functions/ConditionedFunctions',
                    ),

                    'Classes' => array(
                            'Classes'           => 'Classes/Classnames',
                            'Anonymous Classes' => 'Classes/Anonymous',
                            'Class aliases'     => 'Classes/ClassAliasUsage',

                            'Abstract classes'  => 'Classes/Abstractclass',
                            'Interfaces'        => 'Interfaces/Interfacenames',
                            'Traits'            => 'Traits/Traitnames',

                            'Static properties' => 'Classes/StaticProperties',
                            
                            'Static methods'    => 'Classes/StaticMethods',
                            'Abstract methods'  => 'Classes/Abstractmethods',
                            'Final methods'     => 'Classes/Finalmethod',

                            'Class constants'   => 'Classes/ConstantDefinition',
                            'Overwritten constants' => 'Classes/OverwrittenConst',

                            'Magic methods'     => 'Classes/MagicMethod',
                            'Cloning'           => 'Classes/CloningUsage',
                            'Dynamic class call'=> 'Classes/VariableClasses',

                            'PHP 4 constructor' => 'Classes/OldStyleConstructor',
                            'Multiple class in one file' => 'Classes/MultipleClassesInFile',
                    ),

                    'Constants' => array(
                            'Constants'           => 'Constants/ConstantUsage',
                            'Variable Constant'   => 'Constants/VariableConstant',
                            'PHP constants'       => 'Constants/PhpConstantUsage',
                            'PHP Magic constants' => 'Constants/MagicConstantUsage',
                            'Conditioned constant'=> 'Constants/ConditionedConstants',
                    ),

                    'Numbers' => array(
                            'Integers'    => 'Type/Integer',
                            'Hexadecimal' => 'Type/Hexadecimal',
                            'Octal'       => 'Type/Octal',
                            'Binary'      => 'Type/Binary',
                            'Real'        => 'Type/Real',
                    ),

                    'Strings' => array(
                            'Heredoc'    => 'Type/Heredoc',
                            'Nowdoc'     => 'Type/Nowdoc',
                     ),
                    
                    'Errors' => array(
                            'Throw exceptions' => 'Php/ThrowUsage',
                            'Try/Catch'        => 'Php/TryCatchUsage',
                            'Multiple catch'   => 'Structures/MultipleCatch',
                            'Finally'          => 'Structures/TryFinally',
                            'Trigger error'    => 'Php/TriggerErrorUsage',
                     ),

                    'External systems' => array(
                            'System'           => 'Structures/ShellUsage',
                            'Files'            => 'Structures/FileUsage',
                            'LDAP'             => 'Extensions/Extldap',
                            'mail'             => 'Structures/MailUsage',
                     ),

                    'Extensions' => array(
                            'ext/amqp'       => 'Extensions/Extamqp',
                            'ext/apache'     => 'Extensions/Extapache',
                            'ext/apc'        => 'Extensions/Extapc',
                            'ext/apcu'       => 'Extensions/Extapcu',
                            'ext/array'      => 'Extensions/Extarray',
                            'ext/ast'        => 'Extensions/Extast',
                            'ext/bcmath'     => 'Extensions/Extbcmath',
                            'ext/bzip2'      => 'Extensions/Extbzip2',
                            'ext/cairo'      => 'Extensions/Extcairo',
                            'ext/calendar'   => 'Extensions/Extcalendar',
                            'ext/com'        => 'Extensions/Extcom',
                            'ext/crypto'     => 'Extensions/Extcrypto',
                            'ext/ctype'      => 'Extensions/Extctype',
                            'ext/curl'       => 'Extensions/Extcurl',
                            'ext/cyrus'      => 'Extensions/Extcyrus',
                            'ext/date'       => 'Extensions/Extdate',
                            'ext/dba'        => 'Extensions/Extdba',
                            'ext/dio'        => 'Extensions/Extdio',
                            'ext/dom'        => 'Extensions/Extdom',
                            'ext/eaccelerator' => 'Extensions/Exteaccelerator',
                            'ext/enchant'    => 'Extensions/Extenchant',
                            'ext/ereg'       => 'Extensions/Extereg',
                            'ext/event'      => 'Extensions/Extevent',
                            'ext/ev'         => 'Extensions/Extev',
                            'ext/exif'       => 'Extensions/Extexif',
                            'ext/expect'     => 'Extensions/Extexpect',
                            'ext/fdf'        => 'Extensions/Extfdf',
                            'ext/ffmpeg'     => 'Extensions/Extffmpeg',
                            'ext/file'       => 'Extensions/Extfile',
                            'ext/fileinfo'   => 'Extensions/Extfileinfo',
                            'ext/filter'     => 'Extensions/Extfilter',
                            'ext/fpm'        => 'Extensions/Extfpm',
                            'ext/ftp'        => 'Extensions/Extftp',
                            'ext/gd'         => 'Extensions/Extgd',
                            'ext/gearman'    => 'Extensions/Extgearman',
                            'ext/geoip'      => 'Extensions/Extgeoip',
                            'ext/gettext'    => 'Extensions/Extgettext',
                            'ext/gmagick'    => 'Extensions/Extgmagick',
                            'ext/gmp'        => 'Extensions/Extgmp',
                            'ext/gnupg'      => 'Extensions/Extgnupg',
                            'ext/hash'       => 'Extensions/Exthash',
                            'ext/ibase'      => 'Extensions/Extibase',
                            'ext/iconv'      => 'Extensions/Exticonv',
                            'ext/iis'        => 'Extensions/Extiis',
                            'ext/imagick'    => 'Extensions/Extimagick',
                            'ext/imap'       => 'Extensions/Extimap',
                            'ext/info'       => 'Extensions/Extinfo',
                            'ext/inotify'    => 'Extensions/Extinotify',
                            'ext/intl'       => 'Extensions/Extintl',
                            'ext/json'       => 'Extensions/Extjson',
                            'ext/kdm5'       => 'Extensions/Extkdm5',
                            'ext/ldap'       => 'Extensions/Extldap',
                            'ext/libevent'   => 'Extensions/Extlibevent',
                            'ext/libxml'     => 'Extensions/Extlibxml',
                            'ext/mail'       => 'Extensions/Extmail',
                            'ext/mailparse'  => 'Extensions/Extmailparse',
                            'ext/math'       => 'Extensions/Extmath',
                            'ext/mbstring'   => 'Extensions/Extmbstring',
                            'ext/mcrypt'     => 'Extensions/Extmcrypt',
                            'ext/memcache'   => 'Extensions/Extmemcache',
                            'ext/memcached'  => 'Extensions/Extmemcached',
                            'ext/ming'       => 'Extensions/Extming',
                            'ext/mongo'      => 'Extensions/Extmongo',
                            'ext/mssql'      => 'Extensions/Extmssql',
                            'ext/mysql'      => 'Extensions/Extmysql',
                            'ext/mysqli'     => 'Extensions/Extmysqli',
                            'ext/ob'         => 'Extensions/Extob',
                            'ext/oci8'       => 'Extensions/Extoci8',
                            'ext/odbc'       => 'Extensions/Extodbc',
                            'ext/opcache'    => 'Extensions/Extopcache',
                            'ext/openssl'    => 'Extensions/Extopenssl',
                            'ext/parsekit'   => 'Extensions/Extparsekit',
                            'ext/pcntl'      => 'Extensions/Extpcntl',
                            'ext/pcre'       => 'Extensions/Extpcre',
                            'ext/pdo'        => 'Extensions/Extpdo',
                            'ext/pgsql'      => 'Extensions/Extpgsql',
                            'ext/phalcon'    => 'Extensions/Extphalcon',
                            'ext/phar'       => 'Extensions/Extphar',
                            'ext/posix'      => 'Extensions/Extposix',
                            'ext/proctitle'  => 'Extensions/Extproctitle',
                            'ext/pspell'     => 'Extensions/Extpspell',
                            'ext/readline'   => 'Extensions/Extreadline',
                            'ext/recode'     => 'Extensions/Extrecode',
                            'ext/redis'      => 'Extensions/Extredis',
                            'ext/reflexion'  => 'Extensions/Extreflexion',
                            'ext/runkit'     => 'Extensions/Extrunkit',
                            'ext/sem'        => 'Extensions/Extsem',
                            'ext/session'    => 'Extensions/Extsession',
                            'ext/shmop'      => 'Extensions/Extshmop',
                            'ext/simplexml'  => 'Extensions/Extsimplexml',
                            'ext/snmp'       => 'Extensions/Extsnmp',
                            'ext/soap'       => 'Extensions/Extsoap',
                            'ext/sockets'    => 'Extensions/Extsockets',
                            'ext/spl'        => 'Extensions/Extspl',
                            'ext/sqlite'     => 'Extensions/Extsqlite',
                            'ext/sqlite3'    => 'Extensions/Extsqlite3',
                            'ext/sqlsrv'     => 'Extensions/Extsqlsrv',
                            'ext/ssh2'       => 'Extensions/Extssh2',
                            'ext/standard'   => 'Extensions/Extstandard',
                            'ext/tidy'       => 'Extensions/Exttidy',
                            'ext/tokenizer'  => 'Extensions/Exttokenizer',
                            'ext/trader'     => 'Extensions/Exttrader',
                            'ext/wddx'       => 'Extensions/Extwddx',
                            'ext/wikidiff2'  => 'Extensions/Extwikidiff2',
                            'ext/wincache'   => 'Extensions/Extwincache',
                            'ext/xcache'     => 'Extensions/Extxcache',
                            'ext/xdebug'     => 'Extensions/Extxdebug',
                            'ext/xdiff'      => 'Extensions/Extxdiff',
                            'ext/xhprof'     => 'Extensions/Extxhprof',
                            'ext/xml'        => 'Extensions/Extxml',
                            'ext/xmlreader'  => 'Extensions/Extxmlreader',
                            'ext/xmlrpc'     => 'Extensions/Extxmlrpc',
                            'ext/xmlwriter'  => 'Extensions/Extxmlwriter',
                            'ext/xsl'        => 'Extensions/Extxsl',
                            'ext/yaml'       => 'Extensions/Extyaml',
                            'ext/yis'        => 'Extensions/Extyis',
                            'ext/zip'        => 'Extensions/Extzip',
                            'ext/zlib'       => 'Extensions/Extzlib',
                            'ext/zmq'        => 'Extensions/Extzmq',
//                          'ext/skeleton'   => 'Extensions/Extskeleton',
                    ),
                );

    public function collect() {
        // collecting information for Extensions
        $themed = \Analyzer\Analyzer::getThemeAnalyzers('Appinfo');
    
        foreach($this->extensions as $section => $hash) {
            $this->array[$section] = array();

            foreach($hash as $name => $ext) {
                if (($a = \Analyzer\Analyzer::getClass($ext)) === null) {
                    display( "'$ext' is not a class ($name). Ignoring\n");
                    continue;
                }
                if (!in_array($ext, $themed)) {
                    display( "$ext was not analyzed in Appinfo.");
                }
                
                $queryTemplate = "g.idx('analyzers')[['analyzer':'Analyzer\\\\".str_replace('/', '\\\\', $ext)."']].hasNot('notCompatibleWithPhpVersion', null).count()"; 
                $vertices = $this->query($queryTemplate);
                $v = $vertices[0];
                if ($v == 1) {
                    $this->array[$section][$name] = 'Incomp.';
                    continue ;
                } 

                $queryTemplate = "g.idx('analyzers')[['analyzer':'Analyzer\\\\".str_replace('/', '\\\\', $ext)."']].count()"; 
                $vertices = gremlin_query($queryTemplate);
                $v = $vertices->results[0];
                if ($v == 0) {
                    $this->array[$section][$name] = 'Not run';
                    continue;
                } 

                $analyzer = \Analyzer\Analyzer::getInstance($a);
                $this->array[$section][$name] = $analyzer->hasResults() == 'true' ? 'Yes' : 'No';
            }
            
            if ($section == 'Extensions') {
                $list = $this->array[$section];
                uksort($this->array[$section], function ($ka, $kb) use ($list) {
                    if ($list[$ka] == $list[$kb]) {
                        if ($ka > $kb)  { return  1; }
                        if ($ka == $kb) { return  0; }
                        if ($ka > $kb)  { return -1; }
                    } else {
                        return $list[$ka] == 'Yes' ? -1 : 1;
                    }
                });
            }
        }
    
        // collecting information for Composer
        $packages = \Analyzer\Analyzer::getInstance('Composer/PackagesNames');
        
        if ($packages->hasResults()) {
            $this->array['Composer Packages'] = $packages->toArray();
        } else {
            unset($this->array['Composer Packages']);
        }
    }
}

?>
