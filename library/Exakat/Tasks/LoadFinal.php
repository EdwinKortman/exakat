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

use Exakat\Analyzer\Themes;
use Exakat\Config;
use Exakat\Data\Methods;
use Exakat\Data\Dictionary;
use Exakat\Tokenizer\Token;
use Exakat\Exceptions\GremlinException;

class LoadFinal extends Tasks {
    const CONCURENCE = self::ANYTIME;

    private $linksIn = '';

    private $PHPconstants = array();
    private $PHPfunctions = array();
    private $dictCode = null;

    public function run() {
        $this->linksIn = Token::linksAsList();

        $this->dictCode = new Dictionary($this->datastore);

        $this->logTime('Start');
        display('Start load final');

        $this->init();

        $this->makeClassConstantDefinition();

        $this->fixFullnspathConstants();
        $this->fixFullnspathFunctions();
        $this->fixConstantsValue();

        $this->spotPHPNativeConstants();
        $this->spotPHPNativeFunctions();

        $this->spotFallbackConstants();
        
        $this->setConstantDefinition();

        $this->propagateConstants();

        display('End load final');
        $this->logTime('Final');
    }

    private function logTime($step) {
        static $log, $begin, $end, $start;

        if ($log === null) {
            $log = fopen($this->config->projects_root.'/projects/'.$this->config->project.'/log/loadfinal.timing.csv', 'w+');
            if ($log === false) {
                return;
            }
        }

        $end = microtime(true);
        if ($begin === null) {
            $begin = $end;
            $start = $end;
        }

        fwrite($log, $step."\t".($end - $begin)."\t".($end - $start)."\n");
        $begin = $end;
    }

    private function fixFullnspathFunctions() {
        display("fixing Fullnspath for Functions");
        // fix path for constants with Const
        $query = <<<GREMLIN
g.V().hasLabel("Functioncall")
     .has("fullnspath")
     .as("identifier")
     .sideEffect{ cc = it.get().value("fullnspath"); }
     .in("DEFINITION").hasLabel("Function")
     .filter{ actual = it.get().value("fullnspath"); actual != cc;}
     .select("identifier")
     .sideEffect{ it.get().property("fullnspath", actual); }
     .count()
GREMLIN;

        $res = $this->gremlin->query($query);
        display("Fixed Fullnspath for Functions");
    }
    
    private function fixFullnspathConstants() {
        display("fixing Fullnspath for Constants");
        // fix path for constants with Const
        $query = <<<GREMLIN
g.V().hasLabel("Identifier", "Nsname")
     .has("fullnspath")
     .as("identifier")
     .sideEffect{ cc = it.get().value("fullnspath"); }
     .in("DEFINITION").hasLabel("Class", "Trait", "Interface", "Constant", "Defineconstant")
     .coalesce( __.out("ARGUMENT").has("rank", 0), 
                __.hasLabel("Constant").out('NAME'), 
                filter{ true; })
     .filter{ actual = it.get().value("fullnspath"); actual != cc;}
     .select("identifier")
     .sideEffect{ it.get().property("fullnspath", actual); }
     .count()
GREMLIN;

        $res = $this->gremlin->query($query);
        display("Fixed Fullnspath for Constants");
    }

    private function fixConstantsValue() {
        display("fixing values for Constants");
        // fix path for constants with Const
        $query = <<<GREMLIN
g.V().hasLabel("Identifier", "Nsname")
     .not(has("noDelimiter"))
     .as("identifier")
     .in("DEFINITION").hasLabel("Constant", "Defineconstant")
     .coalesce( __.out("ARGUMENT").has("rank", 1), 
                __.hasLabel("Constant").out('VALUE'))
     .has('noDelimiter')
     .sideEffect{ actual = it.get().value("noDelimiter");}
     .select("identifier")
     .sideEffect{ it.get().property("noDelimiter", actual); }
     .count()
GREMLIN;
        $res = $this->gremlin->query($query);

        $query = <<<GREMLIN
g.V().hasLabel("Staticconstant")
     .as("identifier")
     .out("CONSTANT").sideEffect{ name = it.get().value("code"); }.in("CONSTANT")
     .out("CLASS").in("DEFINITION").out("CONST")
     .out("CONST").out("NAME").filter{it.get().value("code") == name; }.in("NAME")
     .out("VALUE").has('noDelimiter')
     .sideEffect{ actual = it.get().value("noDelimiter");}
     .select("identifier")
     .sideEffect{ it.get().property("noDelimiter", actual); }
     .count()
GREMLIN;
        $res = $this->gremlin->query($query);

        display("Fixed values for Constants");
    }

    private function spotPHPNativeConstants() {
        $title = 'mark PHP native constants call';
        $constants = call_user_func_array('array_merge', $this->PHPconstants);
        $constants = array_filter($constants, function ($x) { return strpos($x, '\\') === false;});
        // May be array_keys
        $constantsPHP = array_values($constants);

        $query = <<<GREMLIN
g.V().hasLabel("Identifier")
     .has("fullnspath")
     .not(where( __.in("DEFINITION", "NAME")))
     .values("code")
     .unique()
GREMLIN;
        $res = $this->gremlin->query($query);

        $constants = array_values(array_intersect($res->toArray(), $this->dictCode->translate($constantsPHP) ));
        
        $query = <<<GREMLIN
g.V().hasLabel("Identifier")
     .has("fullnspath")
     .not(where( __.in("DEFINITION")))
     .filter{ it.get().value("code") in arg1 }
     .sideEffect{
         tokens = it.get().value("fullnspath").tokenize('\\\\');
         fullnspath = "\\\\" + tokens.last();
         it.get().property("fullnspath", fullnspath); 
     }.count();

GREMLIN;

        $this->runQuery($query, $title, array('arg1' => $constants));
    }
    
    private function spotPHPNativeFunctions() {
        $title = 'mark PHP native functions call';
        $functions = call_user_func_array('array_merge', $this->PHPfunctions);
        $functions = array_filter($functions, function ($x) { return strpos($x, '\\') === false;});
        $functions = array_map('strtolower', $functions);

        // This weird trick for janusgraph...
        $functions = array_values($functions);

        $query = <<<GREMLIN
g.V().hasLabel("Functioncall")
     .has("fullnspath")
     .not(where( __.in("DEFINITION")))
     .filter{ parts = it.get().value('fullnspath').tokenize('\\\\'); parts.size() > 1 }
     .map{ parts.last().toLowerCase() }
     .unique()
GREMLIN;

        $res = $this->gremlin->query($query);
        if (empty($res->toArray())) {
            return;
        }
        
        $functions = array_values(array_intersect($res->toArray(), $functions));

        $query = <<<GREMLIN
g.V().hasLabel("Functioncall")
     .has("fullnspath")
     .not(has("token", "T_NS_SEPARATOR"))
     .not(where( __.in("DEFINITION")))
     .filter{ parts = it.get().value('fullnspath').tokenize('\\\\'); parts.size() > 1 }
     .filter{ name = parts.last().toLowerCase(); name in arg1 }
     .sideEffect{
         fullnspath = "\\\\" + name;
         it.get().property("fullnspath", fullnspath); 
     }.count();

GREMLIN;

        $this->runQuery($query, $title, array('arg1' => $functions));
    }

    private function runQuery($query, $title, $args = array()) {
        display($title);

        $this->logTime($title);

        try {
            $this->gremlin->query($query, $args);
        } catch (GremlinException $e) {
            // This should be handled nicely!!!
        }

        display('   /'.$title);
        $this->logTime('end '.$title);
    }

    private function spotFallbackConstants() {
        $this->logTime('spotFallbackConstants');
        // Define-style constant definitions
        $query = <<<GREMLIN
g.V().hasLabel("Defineconstant")
     .out("ARGUMENT").has("rank", 0)
     .hasLabel("String").has("noDelimiter").not( has("noDelimiter", '') )
     .map{ 
           s = it.get().value("noDelimiter").toString();
           if ( s.substring(0,1) != "\\\\") {
               s = "\\\\" + s;
           }
           s;
         }.unique();
GREMLIN;
        $constantsDefine = $this->gremlin->query($query)->toArray();

        $query = <<<GREMLIN
g.V().hasLabel("Const")
     .not( where( __.in("CONST") ) ) 
     .out("CONST")
     .out("NAME")
     .filter{ (it.get().value("fullnspath") =~ "^\\\\\\\\[^\\\\\\\\]+\\$").getCount() == 1 }
     .values('fullnspath').unique();

GREMLIN;
        $constantsConst = $this->gremlin->query($query)->toArray();

        $constants = array_merge($constantsConst, $constantsDefine);
        $this->logTime('constants : '.count($constants));

        if (!empty($constantsConst)) {
            $query = <<<GREMLIN
g.V().hasLabel("Identifier", "Nsname")
     .not( where( __.in("NAME", "METHOD", "MEMBER", "EXTENDS", "IMPLEMENTS", "CONSTANT", "ALIAS", "CLASS", "DEFINITION", "GROUPUSE") ) )
     .has("token", without("T_CONST", "T_FUNCTION"))
     .filter{ it.get().value("fullnspath") in arg1 }.sideEffect{name = it.get().value("fullnspath"); }
     .addE('DEFINITION')
     .from( 
        g.V().hasLabel("Defineconstant")
             .as("a").out("ARGUMENT").has("rank", 0).hasLabel("String").has('fullnspath')
             .filter{ it.get().value("fullnspath") == name}.select('a')
      ).count();

GREMLIN;
            $this->gremlin->query($query, array('arg1' => $constantsDefine));

            // Second round, with fallback to global constants
            // Based on define() definitions

            $this->logTime('constants define : '.count($constantsDefine));
            if (!empty($constantsDefine)) {
                $query = <<<GREMLIN
g.V().hasLabel("Identifier", "Nsname")
     .not( where( __.in("NAME", "METHOD", "MEMBER", "EXTENDS", "IMPLEMENTS", "CONSTANT", "ALIAS", "CLASS", "DEFINITION", "GROUPUSE") ) )
     .filter{ name = "\\\\" + it.get().value("fullcode"); name in arg1 }
     .sideEffect{
        fullnspath = "\\\\" + it.get().value("code");
        it.get().property("fullnspath", fullnspath); 
     }
     .addE('DEFINITION')
     .from( 
        g.V().hasLabel("Defineconstant")
             .as("a").out("ARGUMENT").has("rank", 0).hasLabel("String").has('fullnspath')
             .filter{ it.get().value("fullnspath") == name}.select('a')
      ).count()

GREMLIN;
                $res = $this->gremlin->query($query, array('arg1' => $constantsDefine));
            }

            $this->logTime('constants const : '.count($constantsConst));
            if (!empty($constantsConst)) {
            // Based on const definitions
                $query = <<<GREMLIN
g.V().hasLabel("Identifier", "Nsname")
     .not( where( __.in("NAME", "DEFINITION", "EXTENDS", "IMPLEMENTS") ) )
     .filter{ name = "\\\\" + it.get().value("fullcode"); 
              name in arg1; }
     .sideEffect{
         it.get().property("fullnspath", name); 
     }
     .addE('DEFINITION')
     .from( 
        g.V().hasLabel("Const")
             .not( where( __.in("CONST") ) ) 
             .out("CONST")
             .out("NAME")
             .filter{ (it.get().value("fullnspath") =~ "^\\\\\\\\[^\\\\\\\\]+\\$").getCount() == 1 }
       )
       .count()

GREMLIN;
                $res = $this->gremlin->query($query, array('arg1' => $constantsConst));
            }
            
            // TODO : handle case-insensitive
            $this->logTime('Constant definitions');
            display('Link constant definitions');
        } else {
            display('Link constant definitions : skipping.');
        }
    }

    private function setConstantDefinition() {
        display('Set constant definitions');

        $query = <<<'GREMLIN'
g.V().hasLabel("Identifier", "Nsname")
     .where(__.sideEffect{ constante = it.get();}.in("DEFINITION").coalesce( __.hasLabel("Constant").out("VALUE"),
                                                                             __.hasLabel("Defineconstant").out("ARGUMENT").has("rank", 1))
     .sideEffect{ 
        if ("intval" in it.get().keys()) {
            constante.property("intval", it.get().value("intval")); 
        }
        if ("boolean" in it.get().keys()) {
            constante.property("boolean", it.get().value("boolean")); 
        }
        if ("strval" in it.get().keys()) {
            constante.property("strval", it.get().value("strval")); 
        }
     }
)
GREMLIN;
        $this->gremlin->query($query);
    }

    private function makeClassConstantDefinition() {
        // Create link between Class constant and definition
        $query = <<<'GREMLIN'
        g.V().hasLabel('Staticconstant').as('first')
.out('CONSTANT').sideEffect{name = it.get().value("code");}.select('first')
.out('CLASS').hasLabel("Identifier", "Nsname").has('fullnspath')
.sideEffect{classe = it.get().value("fullnspath");}.in('DEFINITION')
.where( __.sideEffect{classes = [];}
          .emit(hasLabel("Class")).repeat( out("EXTENDS").in("DEFINITION") ).times(5)
          .out("CONST").hasLabel("Const").out("CONST").as('const')
          .out("NAME").filter{ it.get().value("code") == name; }.select('const')
          .sideEffect{classes.add(it.get()); }
          .fold()
)
.map{classes[0]}.as('theClass')
.addE('DEFINITION').to('first')
GREMLIN;
        $this->gremlin->query($query);
        display('Create link between Class constant and definition');
        $this->logTime('Class::constant definition');
    }


    private function propagateConstants() {
        display("propagating Constant value in Concatenations");
        // fix path for constants with Const
        $query = <<<GREMLIN
g.V().hasLabel("Concatenation")
     .sideEffect{ x = []; }
     .where( __.out("CONCAT").hasLabel("Identifier", "Nsname") )
     .not(where( __.out("CONCAT").not(has("noDelimiter")) ) )
     .where( __.out("CONCAT").order().by("rank").sideEffect{ x.add( it.get().value("noDelimiter") ) }.count() )
     .sideEffect{ it.get().property("noDelimiter", x.join("")); }
     .count();
GREMLIN;

        $res = $this->gremlin->query($query);
        display("propagating Constant value in Concatenations");

//        display("propagating Constant value in Operators such as +, *, . ....");

    }

    private function init() {
        // fallback for PHP and ext, class, function, constant
        // update fullnspath with fallback for functions
        $themes = new Themes($this->config->dir_root.'/data/analyzers.sqlite');

        $exts = $themes->listAllAnalyzer('Extensions');
        $exts[] = 'php_constants';
        $exts[] = 'php_functions';

        foreach($exts as $ext) {
            $inifile = str_replace('Extensions\Ext', '', $ext).'.ini';
            $fullpath = $this->config->dir_root.'/data/'.$inifile;

            $iniFile = parse_ini_file($fullpath);

            if (!empty($iniFile['constants'][0])) {
                $this->PHPconstants[] = $iniFile['constants'];
            }

            if (!empty($iniFile['functions'][0])) {
                $this->PHPfunctions[] = $iniFile['functions'];
            }
        }
    }
}

?>
