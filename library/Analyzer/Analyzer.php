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


namespace Analyzer;

abstract class Analyzer {
    protected $neo4j          = null;
    protected $code           = null;

    protected $description    = null;

    static public $datastore  = null;
    
    protected $rowCount       = 0; // Number of found values
    protected $processedCount = 0; // Number of initial values
    protected $queryCount     = 0; // Number of ran queries
    protected $rawQueryCount  = 0; // Number of ran queries

    private $queries          = array();
    private $queriesArguments = array();
    private $methods          = array();
    private $arguments        = array();
    
    protected $config         = null;
    
    static public $analyzers  = array();
    
    protected $apply          = null;

    protected $phpVersion       = 'Any';
    protected $phpConfiguration = 'Any';
    
    private $path_tmp           = null;

    protected $severity = self::S_NONE; // Default to None.
    const S_CRITICAL = 'Critical';
    const S_MAJOR    = 'Major';
    const S_MINOR    = 'Minor';
    const S_NOTE     = 'Note';
    const S_NONE     = 'None';

    protected $timeToFix = self::T_NONE; // Default to no time (Should not display)
    const T_NONE    = '0';
    const T_INSTANT = '5';
    const T_QUICK   = '30';
    const T_SLOW    = '60';
    const T_LONG    = '360';

    private $isCompatible            = self::UNKNOWN_COMPATIBILITY;
    const COMPATIBLE                 =  0;
    const UNKNOWN_COMPATIBILITY      = -1;
    const VERSION_INCOMPATIBLE       = -2;
    const CONFIGURATION_INCOMPATIBLE = -3;
    
    static public $docs = null;

    public function __construct() {
        $this->analyzer = get_class($this);
        $this->analyzerQuoted = str_replace('\\', '\\\\', $this->analyzer);
        $this->analyzerIsNot($this->analyzer);

        $this->code = $this->analyzer;
        
        self::initDocs();
        
        $this->apply = new AnalyzerApply();
        $this->apply->setAnalyzer($this->analyzer);
        
        $this->description = new \Description($this->analyzer);
    }
    
    public function __destruct() {
        if ($this->path_tmp !== null) {
            unlink($this->path_tmp);
        }
    }
    
    public function setConfig($config) {
        $this->config = $config;
    }
    
    static public function initDocs() {
        if (Analyzer::$docs === null) {
            $is_phar  = (strpos(basename(dirname(dirname(__DIR__))), '.phar') !== false);
            if ($is_phar === true) {
                $pathDocs = 'phar://'.basename(dirname(dirname(__DIR__))).'/data/analyzers.sqlite';
            } else {
                $pathDocs = dirname(dirname(dirname(__FILE__))).'/data/analyzers.sqlite';
            }
            self::$docs = new Docs($pathDocs);
        }
    }
    
    public static function getClass($name) {
        // accepted names :
        // PHP full name : Analyzer\\Type\\Class
        // PHP short name : Type\\Class
        // Human short name : Type/Class
        // Human shortcut : Class (must be unique among the classes)

        if (strpos($name, '\\') !== false) {
            if (substr($name, 0, 9) == 'Analyzer\\') {
                $class = $name;
            } else {
                $class = 'Analyzer\\'.$name;
            }
        } elseif (strpos($name, '/') !== false) {
            $class = 'Analyzer\\'.str_replace('/', '\\', $name);
        } elseif (strpos($name, '/') === false) {
            self::initDocs();
            $found = self::$docs->guessAnalyzer($name);
            if (count($found) == 0) {
                return false; // no class found
            } elseif (count($found) == 1) {
                $class = $found[0];
            } else {
                // too many options here...
                return false;
            }
        } else {
            $class = $name;
        }
        
        if (class_exists($class)) {
            $actualClassName = new \ReflectionClass($class);
            if ($class !== $actualClassName->getName()) {
                // problems with the case
                return false;
            } else {
                return $class;
            }
        } else {
            return false;
        }
    }
    
    public static function getSuggestionClass($name) {
        self::initDocs();
        $list = self::$docs->listAllAnalyzer();
        $r = array();
        foreach($list as $c) {
            $l = levenshtein($c, $name);

            if ($l < 8) {
                $r[] = $c;
            }
        }
        
        return $r;
    }
    
    public static function getInstance($name) {
        if ($analyzer = static::getClass($name)) {
            return new $analyzer();
        } else {
            echo "No such class as '", $name, "'\n";
            return null;
        }
    }
    
    public function getDescription() {
        return $this->description;
    }

    static public function getThemeAnalyzers($theme) {
        self::initDocs();
        return Analyzer::$docs->getThemeAnalyzers($theme);
    }

    public function getThemes() {
        $analyzer = str_replace('\\', '/', substr(get_class($this), 9));
        return Analyzer::$docs->getThemeForAnalyzer($analyzer);
    }

    public function getAppinfoHeader($lang = 'en') {
        if ($this->appinfo === null) {
            $this->getDescription($lang);
        }

        return $this->appinfo;
    }
    
    static public function getAnalyzers($theme) {
        return Analyzer::$analyzers[$theme];
    }

    private function addMethod($method, $arguments = null) {
        if ($arguments === null) { // empty
            $this->methods[] = $method;
        } elseif (func_num_args() > 2) {
            $arguments = func_get_args();
            array_shift($arguments);
            $argnames = array(str_replace('***', '%s', $method));
            foreach($arguments as $arg) {
                $argname = 'arg'.(count($this->arguments));
                $this->arguments[$argname] = $arg;
                $argnames[] = $argname;
            }
            $this->methods[] = call_user_func_array('sprintf', $argnames);
        } else { // one argument
            $argname = 'arg'.count($this->arguments);
            $this->arguments[$argname] = $arguments;
            $this->methods[] = str_replace('***', $argname, $method);
        }

        return $this;
    }
    
    public function init() {
        $result = $this->query("g.getRawGraph().index().existsForNodes('analyzers');");
        if ($result[0] == 0) {
            $this->query("g.createIndex('analyzers', Vertex)");
        }
        
        $query = "g.idx('analyzers')[['analyzer':'{$this->analyzerQuoted}']]";
        $res = $this->query($query);
        
        if (isset($res[0]) && count($res[0]) == 1) {
            $query = <<<GREMLIN
g.idx('analyzers')[['analyzer':'{$this->analyzerQuoted}']].outE('ANALYZED').each{
    g.removeEdge(it);
}

GREMLIN;
            $this->query($query);
        } else {
            $this->code = addslashes($this->code);
            $query = <<<GREMLIN
x = g.addVertex(null, [analyzer:'{$this->analyzerQuoted}', analyzer:true, line:0, description:'Analyzer index for {$this->analyzerQuoted}', code:'{$this->code}', fullcode:'{$this->code}',  atom:'Index', token:'T_INDEX']);

g.idx('analyzers').put('analyzer', '{$this->analyzerQuoted}', x);

GREMLIN;
            $this->query($query);
        }
    }

    public function isDone() {
        $result = $this->query("g.getRawGraph().index().existsForNodes('analyzers');");
        if ($result[0] == 0) {
            $this->query("g.createIndex('analyzers', Vertex)");

            return false;
        }
        
        $query = "g.idx('analyzers')[['analyzer':'{$this->analyzerQuoted}']].count() == 1";
        $res = $this->query($query);
        return (bool) $res[0][0];
    }

    public function checkphpConfiguration($Php) {
        // this handles Any version of PHP
        if ($this->phpConfiguration == 'Any') {
            return true;
        }
        
        foreach($this->phpConfiguration as $ini => $value) {
            if ($Php->getConfiguration($ini) != $value) {
                return false;
            }
        }
        
        return true;
    }
    
    public function checkPhpVersion($version) {
        // this handles Any version of PHP
        if ($this->phpVersion === 'Any') {
            return true;
        }

        // version and above
        if ((substr($this->phpVersion, -1) === '+') && version_compare($version, $this->phpVersion) >= 0) {
            return true;
        }

        // up to version
        if ((substr($this->phpVersion, -1) === '-') && version_compare($version, $this->phpVersion) < 0) {
            return true;
        }

        // version range 1.2.3-4.5.6
        if (strpos($this->phpVersion, '-') !== false) {
            list($lower, $upper) = explode('-', $this->phpVersion);
            if (version_compare($version, $lower) >= 0 && version_compare($version, $upper) <= 0) {
                return true;
            } else {
                return false;
            }
        }
        
        // One version only
        if (version_compare($version, $this->phpVersion) == 0) {
            return true;
        }
        
        // Default behavior if we don't understand :
        return false;
    }

    // @doc return the list of dependences that must be prepared before the execution of an analyzer
    // @doc by default, nothing.
    public function dependsOn() {
        return array();
    }
    
    public function setApplyBelow($applyBelow = true) {
        $this->apply->setApplyBelow($applyBelow);
        
        $this->addMethod('sideEffect{ applyBelowRoot = it }');
        
        return $this;
    }

    public function query($queryString, $arguments = null) {
        try {
            $result = gremlin_query($queryString, $arguments);
        } catch (\Exceptions\GremlinException $e) {
            display($e->getMessage().
                    $queryString, "\n");
            $result = new \StdClass();
            $result->processed = 0;
            $result->total = 0;
            return array($result);
        }

        if (!isset($result->results)) {
            return array();
        }
        
        return $result->results;
    }

    public function _as($name) {
        $this->methods[] = 'as("'.$name.'")';
        
        return $this;
    }

    public function back($name) {
        $this->methods[] = 'back(\''.$name.'\')';
        
        return $this;
    }
    
    public function ignore() {
        // used to execute some code but not collect any node
        $this->methods[] = 'filter{ 1 == 0; }';
    }

    public function tokenIs($atom) {
        if (is_array($atom)) {
            $this->addMethod('filter{it.token in *** }', $atom);
        } else {
            $this->addMethod('has("token", ***)', $atom);
        }
        
        return $this;
    }

    public function tokenIsNot($atom) {
        if (is_array($atom)) {
            $this->addMethod('filter{!(it.token in ***)}', $atom);
        } else {
            $this->addMethod('hasNot("token", ***)', $atom);
        }
        
        return $this;
    }
    
    public function atomIs($atom) {
        if (is_array($atom)) {
            $this->addMethod('filter{it.atom in ***}', $atom);
        } else {
            $this->addMethod('has("atom", ***)', $atom);
        }
        
        return $this;
    }

    public function atomIsNot($atom) {
        if (is_array($atom)) {
            $this->addMethod('filter{!(it.atom in ***) }', $atom);
        } else {
            $this->addMethod('hasNot("atom", ***)', $atom);
        }
        
        return $this;
    }

    public function atomFunctionIs($atom) {
        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
             ->fullnspath($this->makeFullNsPath($atom));
             
        return $this;
    }

    public function classIs($class) {
        if (is_array($class)) {
            $this->addMethod('as("classIs").in.loop(1){!(it.object.token in ["T_CLASS", "T_FILENAME"])}.filter{it.token != "T_CLASS" || it.out("NAME").next().code in ***}.back("classIs")', $class);
        } elseif ($class == 'Global') {
            $this->addMethod('as("classIs").in.loop(1){!(it.object.token in ["T_CLASS", "T_FILENAME"])}.filter{it.token != "T_CLASS"}.back("classIs")');
        } else {
            $this->addMethod('as("classIs").in.loop(1){!(it.object.token in ["T_CLASS", "T_FILENAME"])}.filter{it.token != "T_CLASS" || it.out("NAME").next().code != ***}.back("classIs")', $class);
        }
        
        return $this;
    }

    public function classIsNot($class) {
        if (is_array($class)) {
            $this->addMethod('as("classIsNot").in.loop(1){!(it.object.token in ["T_CLASS", "T_FILENAME"])}.filter{it.token == "T_FILENAME" || !(it.out("NAME").next().code in ***)}.back("classIsNot")', $class);
        } elseif ($class == 'Global') {
            $this->addMethod('as("classIsNot").in.loop(1){!(it.object.token in ["T_CLASS"])}.back("classIsNot")');
        } else {
            $this->addMethod('as("classIsNot").in.loop(1){!(it.object.token in ["T_CLASS", "T_FILENAME"])}.filter{it.token == "T_FILENAME" || it.out("NAME").next().code != ***}.back("classIsNot")', $class);
        }
        
        return $this;
    }

    public function traitIs($trait) {
        if (is_array($trait)) {
            $this->addMethod('as("traitIs").in.loop(1){!(it.object.token in ["T_TRAIT", "T_FILENAME"])}.filter{it.token != "T_TRAIT" || !(it.out("NAME").next().code in ***)}.back("traitIs")', $trait);
        } elseif ($trait == 'Global') {
            $this->addMethod('as("traitIs").in.loop(1){!(it.object.token in ["T_TRAIT", "T_FILENAME"])}.filter{it.token != "T_TRAIT"}.back("traitIs")');
        } else {
            $this->addMethod('as("traitIs").in.loop(1){!(it.object.token in ["T_TRAIT", "T_FILENAME"])}.filter{it.token != "T_TRAIT" || it.out("NAME").next().code != ***}.back("traitIs")', $trait);
        }
        
        return $this;
    }

    public function traitIsNot($trait) {
        if (is_array($trait)) {
            $this->addMethod('as("traitIsNot").in.loop(1){!(it.object.token in ["T_TRAIT", "T_FILENAME"])}.filter{it.token == "T_FILENAME" || !(it.out("NAME").next().code in ***)}.back("traitIsNot")', $trait);
        } elseif ($trait == 'Global') {
            $this->addMethod('as("traitIsNot").in.loop(1){!(it.object.token in ["T_TRAIT"])}.back("traitIsNot")');
        } else {
            $this->addMethod('as("traitIsNot").in.loop(1){!(it.object.token in ["T_TRAIT", "T_FILENAME"])}.filter{it.token == "T_FILENAME" || it.out("NAME").next().code != ***}.back("traitIsNot")', $trait);
        }
        
        return $this;
    }
    
    public function functionIs($function) {
        if (is_array($function)) {
            $this->addMethod('as("functionIs").in.loop(1){!(it.object.token in ["T_FUNCTION", "T_FILENAME"])}.filter{it.token != "T_FILENAME" || it.out("NAME").next().code in ***}.back("functionIs")', $function);
        } elseif ($function == 'Global') {
            $this->addMethod('as("functionIs").in.loop(1){!(it.object.token in ["T_FUNCTION", "T_FILENAME"])}.filter{it.token != "T_FUNCTION"}.back("functionIs")');
        } else {
            $this->addMethod('as("functionIs").in.loop(1){!(it.object.token in ["T_FUNCTION", "T_FILENAME"])}.filter{it.token != "T_FILENAME" || it.out("NAME").next().code != ***}.back("functionIs")', $function);
        }
        
        return $this;
    }

    public function functionIsNot($function) {
        if (is_array($function)) {
                $this->addMethod('as("functionIsNot").in.loop(1){!(it.object.token in ["T_FUNCTION", "T_FILENAME"])}.filter{it.token == "T_FILENAME" || !(it.out("NAME").next().code in ***)}.back("functionIsNot")', $function);
        } elseif ($function == 'Global') {
            $this->addMethod('as("functionIsNot").in.loop(1){!(it.object.token in ["T_FUNCTION"])}.back("functionIsNot")');
        } else {
            $this->addMethod('as("functionIsNot").in.loop(1){!(it.object.token in ["T_FUNCTION", "T_FILENAME"])}.filter{it.token == "T_FILENAME" || it.out("NAME").next().code != ***}.back("functionIsNot")', $function);
        }
        
        return $this;
    }

    public function namespaceIs($namespace) {
        if (is_array($namespace)) {
            $this->addMethod('as("namespaceIs").in.loop(1){!(it.object.token in ["T_NAMESPACE", "T_FILENAME"])}.filter{ it.token == "T_NAMESPACE" && it.code in *** }.back("namespaceIs")', $namespace);
        } elseif ($namespace == 'Global') {
            $this->addMethod('as("namespaceIs").in.loop(1){!(it.object.token in ["T_NAMESPACE", "T_FILENAME"])}.filter{ it.token == "T_FILENAME" || it.out("NAMESPACE").next().code == "Global" }.back("namespaceIs")');
        } else {
            $this->addMethod('as("namespaceIs").in.loop(1){!(it.object.token in ["T_NAMESPACE", "T_FILENAME"])}.filter{ it.token == "T_NAMESPACE" && it.code == *** }.back("namespaceIs")', $namespace);
        }
        
        return $this;
    }

    public function atomInside($atom) {
        if (is_array($atom)) {
            $this->addMethod('out().loop(1){true}{it.object.atom in ***}', $atom);
        } else {
            $this->addMethod('out().loop(1){true}{it.object.atom == ***}', $atom);
        }
        
        return $this;
    }

    public function noAtomInside($atom) {
        if (is_array($atom)) {
            $this->addMethod('filter{ it.out().loop(1){true}{it.object.atom in ***}.any() == false}', $atom);
        } else {
            $this->addMethod('filter{ it.out().loop(1){true}{it.object.atom == ***}.any() == false}', $atom);
        }
        
        return $this;
    }

    public function atomAboveIs($atom) {
        if (is_array($atom)) {
            $this->addMethod('in().loop(1){true}{it.object.atom in ***}', $atom);
        } else {
            $this->addMethod('in().loop(1){true}{it.object.atom == ***}', $atom);
        }
        
        return $this;
    }
    
    public function trim($property, $chars = '\'\"') {
        $this->addMethod('transform{it.'.$property.'.replaceFirst("^['.$chars.']?(.*?)['.$chars.']?\$", "\$1")}');
        
        return $this;
    }

    public function analyzerIs($analyzer) {
        if (is_array($analyzer)) {
            foreach($analyzer as &$a) {
                $a = self::getClass($analyzer);
            }
            unset($a);
            $this->addMethod('filter{ it.in("ANALYZED").filter{ it.code in ***}.any()}', $analyzer);
        } else {
            if ($analyzer == 'self') {
                $analyzer = $this->analyzer;
            } else {
                $analyzer = self::getClass($analyzer);
            }
            $this->addMethod('filter{ it.in("ANALYZED").has("code", ***).any()}', $analyzer);
        }
        
        return $this;
    }

    public function analyzerIsNot($analyzer) {
        if (is_array($analyzer)) {
            foreach($analyzer as &$a) {
                $a = self::getClass($analyzer);
            }
            unset($a);
            $this->addMethod('filter{ it.in("ANALYZED").filter{ it.code in ***}.any() == false}', $analyzer);
        } else {
            if ($analyzer == 'self') {
                $analyzer = $this->analyzer;
            } else {
                $analyzer = self::getClass($analyzer);
            }
            $this->addMethod('filter{ it.in("ANALYZED").has("code", ***).any() == false}', $analyzer);
        }

        return $this;
    }

    public function is($property, $value = true) {
        if ($value === null) {
            $this->addMethod("has('$property', null)");
        } elseif ($value === true) {
            $this->addMethod("has('$property', true)");
        } elseif ($value === false) {
            $this->addMethod("has('$property', false)");
        } else {
            $this->addMethod("filter{ it.$property in ***;}", $value);
        }

        return $this;
    }

    public function isNot($property, $value = true) {
        if ($value === null) {
            $this->addMethod("hasNot('$property', null)");
        } elseif ($value === true) {
            $this->addMethod("hasNot('$property', true)");
        } elseif ($value === false) {
            $this->addMethod("hasNot('$property', false)");
        } else {
            $this->addMethod("filter{ it.$property != ***;}", $value);
        }
        
        return $this;
    }

    public function isMore($property, $value = '0') {
        if (is_int($value)) {
            $this->addMethod("filter{ it.$property > ***;}", $value);
        } else {
            // this is a variable name
            $this->addMethod("filter{ it.$property > $value;}", $value);
        }

        return $this;
    }

    public function isLess($property, $value= '0') {
        if (is_int($value)) {
            $this->addMethod("filter{ it.$property < ***;}", $value);
        } else {
            // this is a variable name
            $this->addMethod("filter{ it.$property < $value;}", $value);
        }

        return $this;
    }

    public function hasRank($value = '0', $link = 'ARGUMENT') {
        if ($value === 'first') {
            // @note : can't use has() with integer!
            $this->addMethod('filter{it.rank == 0}');
        } elseif ($value === 'last') {
            $this->addMethod("filter{it.rank == it.in('$link').out('$link').count() - 1}");
        } elseif ($value === '2last') {
            $this->addMethod("filter{it.rank == it.in('$link').out('$link').count() - 2}");
        } else {
            $this->addMethod('filter{it.rank == ***}', abs(intval($value)));
        }

        return $this;
    }

    public function noChildWithRank($edgeName, $rank = '0') {
        if ($rank === 'first') {
            $this->addMethod("filter{ it.out(***).has('rank','0').any() == false }", $edgeName);
        } elseif ($rank === 'last') {
            $this->addMethod("filter{ it.out(***).has('rank',it.in(***).count() - 1).any() == false }", $edgeName, $edgeName);
        } elseif ($rank === '2last') {
            $this->addMethod("filter{ it.out(***).has('rank',it.in(***).count() - 2).any() == false }", $edgeName, $edgeName);
        } else {
            $this->addMethod("filter{ it.out(***).has('rank', ***).any() == false}", $edgeName, abs(intval($rank)));
        }

        return $this;
    }

    public function code($code, $caseSensitive = false) {
        if ($caseSensitive === true) {
            $caseSensitive = '';
        } else {
            $this->tolowercase($code);
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($code)) {
            $this->addMethod('filter{it.code'.$caseSensitive.' in ***}', $code);
        } else {
            $this->addMethod('filter{it.code'.$caseSensitive.' == ***}', $code);
        }
        
        return $this;
    }

    public function codeIsNot($code, $caseSensitive = false) {
        if ($caseSensitive === true) {
            $caseSensitive = '';
        } else {
            $this->tolowercase($code);
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($code)) {
            $this->addMethod('filter{!(it.code'.$caseSensitive.' in ***)}', $code);
        } else {
            $this->addMethod('filter{it.code'.$caseSensitive.' != ***}', $code);
        }
        
        return $this;
    }

    public function noDelimiter($code, $caseSensitive = false) {
        $this->addMethod('has("atom", "String")', $code);

        if ($caseSensitive === true) {
            $caseSensitive = '';
        } else {
            $this->tolowercase($code);
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($code)) {
            $this->addMethod('filter{it.noDelimiter'.$caseSensitive.' in ***}', $code);
        } else {
            $this->addMethod('filter{it.noDelimiter'.$caseSensitive.' == ***}', $code);
        }
        
        return $this;
    }

    public function noDelimiterIsNot($code, $caseSensitive = false) {
        $this->addMethod('has("atom", "String")', $code);

        if ($caseSensitive === false) {
            $caseSensitive = '';
        } else {
            $this->tolowercase($code);
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($code)) {
            $this->addMethod('filter{!(it.noDelimiter'.$caseSensitive.' in ***)}', $code);
        } else {
            $this->addMethod('filter{it.noDelimiter'.$caseSensitive.' != ***}', $code);
        }
        
        return $this;
    }

    public function fullnspath($code, $caseSensitive = false) {
        if ($caseSensitive === true) {
            $caseSensitive = '';
        } else {
            $this->tolowercase($code);
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($code)) {
            $this->addMethod('filter{it.fullnspath'.$caseSensitive.' in ***}', $code);
        } else {
            $this->addMethod('filter{it.fullnspath'.$caseSensitive.' == ***}', $code);
        }
        
        return $this;
    }

    public function fullnspathIsNot($code, $caseSensitive = false) {
        if ($caseSensitive === true) {
            $caseSensitive = '';
        } else {
            $this->tolowercase($code);
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($code)) {
            $this->addMethod('filter{!(it.fullnspath'.$caseSensitive.' in ***)}', $code);
        } else {
            $this->addMethod('filter{it.fullnspath'.$caseSensitive.' != ***}', $code);
        }
        
        return $this;
    }
    
    public function codeIsPositiveInteger() {
        $this->addMethod('filter{ if( it.code.isInteger()) { it.code > 0; } else { true; }}', null); // may be use toInteger() ?

        return $this;
    }

    public function samePropertyAs($property, $name, $caseSensitive = false) {
        if ($caseSensitive === true || $property == 'line' || $property == 'rank') {
            $caseSensitive = '';
        } else {
            $caseSensitive = '.toLowerCase()';
        }
        $this->addMethod('filter{ it.'.$property.$caseSensitive.' == '.$name.$caseSensitive.'}');

        return $this;
    }

    public function isPropertyIn($property, $name, $caseSensitive = false) {
        if ($caseSensitive === true || $property == 'line' || $property == 'rank') {
            $caseSensitive = '';
        } else {
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($name)) {
            $name = "['". join("', '", $name)."']";
        }

        $this->addMethod('filter{ it.'.$property.$caseSensitive.' in '.$name.'}');
    
        return $this;
    }

    public function isPropertyNotIn($property, $name, $caseSensitive = false) {
        if ($caseSensitive === true || $property == 'line' || $property == 'rank') {
            $caseSensitive = '';
        } else {
            $caseSensitive = '.toLowerCase()';
        }

        if (is_array($name)) {
            $name = "['". join("', '", $name)."']";
            $name = str_replace('\\', '\\\\', $name);
        }

        $this->addMethod('filter{ !(it.'.$property.$caseSensitive.' in '.$name.')}');
    
        return $this;
    }

    public function sameContextAs($storage = 'context', $context = array('Namespace', 'Class', 'Function')) {
        foreach($context as &$c) {
            $c = $storage.'["'.$c.'"] == '.$context.'["'.$c.'"] ';
        }
        unset($c);
        $context = join(' && ', $context);
        
        $this->addMethod('filter{ '.$context.' }');

        return $this;
    }
    
    public function notSamePropertyAs($property, $name, $caseSensitive = false) {
        if ($caseSensitive === true || $property == 'line' || $property == 'rank') {
            $caseSensitive = '';
        } else {
            $caseSensitive = '.toLowerCase()';
        }
        $this->addMethod('filter{ it.'.$property.$caseSensitive.' != '.$name.$caseSensitive.'}');

        return $this;
    }

    public function savePropertyAs($property, $name) {
        $this->addMethod("sideEffect{ $name = it.$property; }");

        return $this;
    }

    public function isGrandParent() {
        $this->addMethod("filter{ fns = it.fullnspath; it.in.loop(1){it.object.atom != 'Class'}{it.object.atom == 'Class'}.out('EXTENDS').transform{ g.idx('classes')[['path':it.fullnspath]].next(); }.loop(2){true}{it.object.fullnspath == fns}.any() }");

        return $this;
    }

    public function isNotGrandParent() {
        $this->addMethod("filter{ fns = it.fullnspath; it.in.loop(1){it.object.atom != 'Class'}{it.object.atom == 'Class'}.out('EXTENDS').transform{ g.idx('classes')[['path':it.fullnspath]].next(); }.loop(2){true}{it.object.fullnspath == fns}.any() == false}");

        return $this;
    }

    public function fullcodeTrimmed($code, $trim = "\"'", $caseSensitive = false) {
        if ($caseSensitive === true) {
            $caseSensitive = '';
        } else {
            $this->tolowercase($code);
            $caseSensitive = '.toLowerCase()';
        }
        
        $trim = addslashes($trim);
        if (is_array($code)) {
            $this->addMethod("filter{it.fullcode$caseSensitive.replaceFirst(\"^[$trim]?(.*?)[$trim]?\\\$\", \"\\\$1\") in ***}", $code);
        } else {
            $this->addMethod("filter{it.fullcode$caseSensitive.replaceFirst(\"^[$trim]?(.*?)[$trim]?\\\$\", \"\\\$1\") == ***}", $code);
        }
        
        return $this;
    }
    
    public function fullcode($code, $caseSensitive = false) {
        if ($caseSensitive === true) {
            $caseSensitive = '';
        } else {
            $this->tolowercase($code);
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($code)) {
            $this->addMethod('filter{it.fullcode'.$caseSensitive.' in ***}', $code);
        } else {
            $this->addMethod('filter{it.fullcode'.$caseSensitive.' == ***}', $code);
        }
        
        return $this;
    }
    
    public function fullcodeIsNot($code, $caseSensitive = false) {
        if ($caseSensitive === true) {
            $caseSensitive = '';
        } else {
            $this->tolowercase($code);
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($code)) {
            $this->addMethod("filter{!(it.fullcode$caseSensitive in ***)}", $code);
        } else {
            $this->addMethod("filter{it.fullcode$caseSensitive != ***}", $code);
        }
        
        return $this;
    }

    public function isUppercase($property = 'fullcode') {
        $this->addMethod("filter{it.$property == it.$property.toUpperCase()}");

        return $this;
    }

    public function isNotLowercase($property = 'fullcode') {
        $this->addMethod("filter{it.$property != it.$property.toLowerCase()}");

        return $this;
    }

    public function filter($filter) {
        $this->addMethod("filter{ $filter }");

        return $this;
    }

    public function codeLength($length = ' == 1 ') {
        // @todo add some tests ? Like Operator / value ?
        $this->addMethod("filter{it.code.length() $length}");

        return $this;
    }

    public function fullcodeLength($length = ' == 1 ') {
        // @todo add some tests ? Like Operator / value ?
        $this->addMethod("filter{it.fullcode.length() $length}");

        return $this;
    }

    public function groupCount($column) {
        $this->addMethod("groupCount(m){it.$column}");
        
        return $this;
    }

    public function eachCounted($variable, $times, $comp = '==') {
        $this->addMethod(<<<GREMLIN
groupBy(m){{$variable}}{it}.iterate();
// This is plugged into each{}
m.findAll{ it.value.size() $comp $times}.values().flatten().each{ n.add(it); }
GREMLIN
);

        return $this;
    }

    public function countIs($comparison) {
        $this->addMethod('aggregate().filter{ it.size '.$comparison.'}', null);
        
        return $this;
    }

    public function regex($column, $regex) {
        $this->addMethod(<<<GREMLIN
filter{ (it.$column =~ "$regex" ).getCount() > 0 }
GREMLIN
);

        return $this;
    }

    public function regexNot($column, $regex) {
        $this->addMethod(<<<GREMLIN
filter{ (it.$column =~ "$regex" ).getCount() == 0 }
GREMLIN
);

        return $this;
    }

    protected function outIs($edgeName) {
        if (is_array($edgeName)) {
            $this->addMethod("out('".implode("', '", $edgeName)."')");
        } else {
            $this->addMethod('out(***)', $edgeName);
        }
        
        return $this;
    }

    // follows a link if it is there (and do nothing otherwise)
    protected function outIsIE($edgeName) {
        if (is_array($edgeName)) {
            $this->addMethod("transform{ a = it; while (a.out('".implode("', '", $edgeName)."').any()) { a = a.out('".implode("', '", $edgeName)."').next(); }; a;}");
        } else {
            $this->addMethod("transform{ a = it; while (a.out('$edgeName').any()) { a = a.out('$edgeName').next(); };  a;}", $edgeName);
        }
        
        return $this;
    }

    public function outIsnt($edgeName) {
        if (is_array($edgeName)) {
            $this->addMethod("filter{ it.out('".implode("', '", $edgeName)."').count() == 0}");
        } else {
            $this->addMethod('filter{ it.out(***).count() == 0}', $edgeName);
        }
        
        return $this;
    }

    public function rankIs($edgeName, $rank) {
        if (is_array($edgeName)) {
            // @todo
            die(" I don't understand arrays in rankIs()");
        }

        if ($rank == 'first') {
            $rank = 0;
            $this->addMethod("out(***).filter{it.getProperty('rank')  == ***}", $edgeName, $rank);
        } elseif ($rank === 'last') {
            $this->addMethod('sideEffect{ rank = it.out(***).count() - 1;}', $edgeName);
            $this->addMethod("out(***).filter{it.getProperty('rank')  == rank}", $edgeName);
        } elseif ($rank === '2last') {
            $this->addMethod('sideEffect{ rank = it.out(***).count() - 2;}', $edgeName);
            $this->addMethod("out(***).filter{it.getProperty('rank')  == rank}", $edgeName);
        } else {
            $rank = abs(intval($rank));
            $this->addMethod("out(***).filter{it.getProperty('rank')  == ***}", $edgeName, $rank);
        }
        
        return $this;
    }

    public function nextSibling($link = 'ELEMENT') {
        $this->addMethod("sideEffect{sibling = it.rank}.in('$link').out('$link').filter{sibling + 1 == it.rank}");

        return $this;
    }

    public function nextSiblings($link = 'ELEMENT') {
        $this->addMethod("sideEffect{sibling = it.rank}.in('$link').out('$link').filter{sibling + 1 <= it.rank}");

        return $this;
    }

    public function previousSibling($link = 'ELEMENT') {
        $this->addMethod("filter{it.rank > 0}.sideEffect{sibling = it.rank}.in('$link').out('$link').filter{sibling - 1 == it.rank}");

        return $this;
    }

    public function previousSiblings($link = 'ELEMENT') {
        $this->addMethod("filter{it.rank > 0}.sideEffect{sibling = it.rank}.in('$link').out('$link').filter{sibling - 1 >= it.rank}");

        return $this;
    }

    public function nextVariable($code) {
        $this->addMethod(<<<GREMLIN
sideEffect{ init = it;}
.filter{ nextVariable = []; it
.in.loop(1){it.object.atom != "Function"}{(it.object.atom == "Function") && (it.object.out("NAME").hasNot("code", "").any())}
.out('BLOCK').out.loop(1){true}{it.object.atom == 'Variable' && it.object.line > init.line && it.object.code == init.code}
.fill(nextVariable);
nextVariable.sort{ it.line}.size() > 0;
}
.transform{ nextVariable[0]}

GREMLIN
);

        return $this;
    }

    public function inIs($edgeName) {
        if (is_array($edgeName)) {
            // @todo
            $this->addMethod('inE.filter{it.label in ***}.outV', $edgeName);
        } else {
            $this->addMethod('in(***)', $edgeName);
        }
        
        return $this;
    }

    // follows a link if it is there (and do nothing otherwise)
    protected function inIsIE($edgeName) {
        if (is_array($edgeName)) {
            $edgeNames = "'" . join("', '", $edgeName)."'";
            $this->addMethod("transform{ a = it; while (a.in($edgeNames).any()) { a = a.in($edgeNames).next(); };  a;}", $edgeName);
        } else {
            $this->addMethod("transform{ a = it; while (a.in('$edgeName').any()) { a = a.in('$edgeName').next(); };  a;}", $edgeName);
        }
        
        return $this;
    }

    public function inIsnot($edgeName) {
        if (is_array($edgeName)) {
            $this->addMethod('filter{ it.inE.filter{ it.label in ***}.any() == false}', $edgeName);
        } else {
            $this->addMethod('filter{ it.in(***).any() == false}', $edgeName);
        }
        
        return $this;
    }

    public function hasIn($edgeName) {
        if (is_array($edgeName)) {
            $this->addMethod('filter{ it.inE.filter{ it.label in ***}.any()}', $edgeName);
        } else {
            $this->addMethod('filter{ it.in(***).any()}', $edgeName);
        }
        
        return $this;
    }

    public function raw($query) {
        ++$this->rawQueryCount;
        $this->addMethod($query);
        
        return $this;
    }
    
    public function hasNoIn($edgeName) {
        if (is_array($edgeName)) {
            $this->addMethod('filter{ it.inE.filter{ it.label in ***}.any() == false}', $edgeName);
        } else {
            $this->addMethod('filter{ it.in(***).any() == false}', $edgeName);
        }
        
        return $this;
    }

    public function hasOut($edgeName) {
        if (is_array($edgeName)) {
            $this->addMethod('filter{ it.outE.filter{ it.label in ***}.inV.any()}', $edgeName);
        } else {
            $this->addMethod('filter{ it.out(***).any()}', $edgeName);
        }
        
        return $this;
    }
    
    public function hasNoOut($edgeName) {
        if (is_array($edgeName)) {
            // @todo
            $this->addMethod('filter{ it.outE.filter{ it.label in ***}.inV.any() == false}', $edgeName);
        } else {
            $this->addMethod('filter{ it.out(***).any() == false}', $edgeName);
        }
        
        return $this;
    }

    public function isInCatchBlock() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Catch"}{(it.object.atom == "Catch")}.any()');
        
        return $this;
    }

    public function isNotInCatchBlock() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Catch"}{(it.object.atom == "Catch")}.any() == false}');
        
        return $this;
    }

    public function hasParent($parentClass, $ins = array()) {
        if (empty($ins)) {
            $in = '.in';
        } else {
            $in = array();
            
            if (!is_array($ins)) {
                $ins = array($ins);
            }
            foreach($ins as $i) {
                if (empty($i)) {
                    $in[] = '.in';
                } else {
                    $in[] = ".in('$i')";
                }
            }
            
            $in = implode('', $in);
        }
        
        if (is_array($parentClass)) {
            $this->addMethod('filter{ it.'.$in.'.filter{ it.atom in ***).count() != 0}', $parentClass);
        } else {
            $this->addMethod('filter{ it.'.$in.'.has("atom", ***).count() != 0}', $parentClass);
        }
        
        return $this;
    }

    public function hasNoParent($parentClass, $ins = array()) {
        
        if (empty($ins)) {
            $in = '.in';
        } else {
            $in = array();
            
            if (!is_array($ins)) {
                $ins = array($ins);
            }
            foreach($ins as $i) {
                if (empty($i)) {
                    $in[] = '.in';
                } else {
                    $in[] = ".in('$i')";
                }
            }
            
            $in = implode('', $in);
        }
        
        if (is_array($parentClass)) {
            $this->addMethod('filter{ it'.$in.'.filter{it.atom in ***}.count() == 0}', $parentClass);
        } else {
            $this->addMethod('filter{ it'.$in.'.has("atom", ***).count() == 0}', $parentClass);
        }
        
        return $this;
    }
    
    public function hasConstantDefinition() {
        $this->addMethod("filter{ g.idx('constants')[['path':it.fullnspath]].any()}");
    
        return $this;
    }

    public function hasNoConstantDefinition() {
        $this->addMethod("filter{ g.idx('constants')[['path':it.fullnspath]].any() == false}");
    
        return $this;
    }

    public function hasFunctionDefinition() {
        $this->addMethod("filter{ g.idx('functions')[['path':it.fullnspath]].any()}");
    
        return $this;
    }

    public function hasNoFunctionDefinition() {
        $this->addMethod("filter{ g.idx('functions')[['path':it.fullnspath]].any() == false}");
    
        return $this;
    }

    public function functionDefinition() {
        $this->addMethod("hasNot('fullnspath', null).transform{ g.idx('functions')[['path':it.fullnspath]].next(); }");
    
        return $this;
    }

    public function goToCurrentScope() {
        $this->addMethod('in.loop(1){!(it.object.atom in ["Function", "Phpcode"])}{(it.object.atom in ["Function", "Phpcode"])}');
        
        return $this;
    }
    
    public function goToFunction() {
        $this->addMethod('in.loop(1){it.object.atom != "Function"}{(it.object.atom == "Function") && (it.object.out("NAME").hasNot("code", "").any())}');
        
        return $this;
    }

    public function notInFunction() {
        $this->notInInstruction('Function');
        
        return $this;
    }

    public function notInInstruction($atom = 'Function') {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "'.$atom.'"}{it.object.atom == "'.$atom.'"}.any() == false}');
        
        return $this;
    }

    public function goToFile() {
        $this->addMethod('in.loop(1){it.object.atom != "File"}{it.object.atom == "File"}');
        
        return $this;
    }

    public function noNamespaceDefinition() {
        $this->addMethod("hasNot('fullnspath', null).filter{ g.idx('namespaces')[['path':it.fullnspath]].any() == false }");
    
        return $this;
    }

    public function classDefinition() {
        $this->addMethod("hasNot('fullnspath', null).transform{ g.idx('classes')[['path':it.fullnspath]].next(); }");
    
        return $this;
    }

    public function noClassDefinition() {
        $this->addMethod("hasNot('fullnspath', null).filter{ g.idx('classes')[['path':it.fullnspath]].any() == false }");
    
        return $this;
    }

    public function hasClassDefinition() {
        $this->addMethod("filter{ g.idx('classes')[['path':it.fullnspath]].any()}");
    
        return $this;
    }

    public function hasNoClassDefinition() {
        $this->addMethod("filter{ g.idx('classes')[['path':it.fullnspath]].any() == false}");
    
        return $this;
    }

    public function interfaceDefinition() {
        $this->addMethod("hasNot('fullnspath', null).transform{ g.idx('interfaces')[['path':it.fullnspath]].next(); }");
    
        return $this;
    }

    public function noInterfaceDefinition() {
        $this->addMethod("hasNot('fullnspath', null).filter{ g.idx('interfaces')[['path':it.fullnspath]].any() == false }");
    
        return $this;
    }

    public function hasInterfaceDefinition() {
        $this->addMethod("filter{ g.idx('interfaces')[['path':it.fullnspath]].any()}");
    
        return $this;
    }

    public function hasNoInterfaceDefinition() {
        $this->addMethod("filter{ g.idx('interfaces')[['path':it.fullnspath]].any() == false}");
    
        return $this;
    }

    public function traitDefinition() {
        $this->addMethod("hasNot('fullnspath', null).transform{ g.idx('traits')[['path':it.fullnspath]].next(); }");
    
        return $this;
    }

    public function noTraitDefinition() {
        $this->addMethod("hasNot('fullnspath', null).filter{ g.idx('traits')[['path':it.fullnspath]].any() == false }");
    
        return $this;
    }
    
    public function groupFilter($characteristic, $percentage) {
        $this->addMethod('sideEffect{'.$characteristic.'}.groupCount(gf){x2}.aggregate().sideEffect{'.$characteristic.'}.filter{gf[x2] < '.$percentage.' * gf.values().sum()}');

        return $this;
    }
    
    public function goToClass() {
        $this->addMethod('in.loop(1){it.object.atom != "Class"}{it.object.atom == "Class"}');
        
        return $this;
    }
    
    public function notInClass() {
        $this->notInInstruction('Class');
        
        return $this;
    }

    public function inClass() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Class"}{it.object.atom == "Class"}.any()}');
        
        return $this;
    }

    public function goToInterface() {
        $this->addMethod('in.loop(1){it.object.atom != "Interface"}{it.object.atom == "Interface"}');
        
        return $this;
    }

    public function notInInterface() {
        $this->notInInstruction('Trait');
        
        return $this;
    }

    public function goToTrait() {
        $this->addMethod('in.loop(1){it.object.atom != "Trait"}{it.object.atom == "Trait"}');
        
        return $this;
    }

    public function notInTrait() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Trait"}{it.object.atom == "Trait"}.any() == false}');
        
        return $this;
    }

    public function goToClassTrait() {
        $this->addMethod('in.loop(1){!(it.object.atom in ["Trait","Class"])}{it.object.atom in ["Trait", "Class"]}');
        
        return $this;
    }

    public function notInClassTrait() {
        $this->addMethod('filter{ it.in.loop(1){!(it.object.atom in ["Trait","Class"])}{it.object.atom  in ["Trait","Class"}.any() == false}');
        
        return $this;
    }

    public function goToClassInterface() {
        $this->addMethod('in.loop(1){!(it.object.atom in ["Interface","Class"])}{it.object.atom in ["Interface", "Class"]}');
        
        return $this;
    }

    public function notInClassInterface() {
        $this->addMethod('filter{ it.in.loop(1){!(it.object.atom in ["Interface","Class"])}{it.object.atom  in ["Interface","Class"]}.any() == false}');
        
        return $this;
    }

    public function goToClassInterfaceTrait() {
        $this->addMethod('in.loop(1){!(it.object.atom in ["Interface","Class","Trait"])}{it.object.atom in ["Interface", "Class","Trait"]}');
        
        return $this;
    }

    public function notInClassInterfaceTrait() {
        $this->addMethod('filter{ it.in.loop(1){!(it.object.atom in ["Interface", "Class", "Trait"])}{it.object.atom in ["Interface", "Class", "Trait"]}.any() == false}');
        
        return $this;
    }
    
    public function goToExtends() {
        $this->addMethod('out("EXTENDS").transform{ g.idx("classes")[["path":it.fullnspath]].next(); }.loop(2){true}{it.object.atom == "Class"}');
        
        return $this;
    }

    public function goToImplements() {
        $this->addMethod('out("IMPLEMENTS").transform{ g.idx("classes")[["path":it.fullnspath]].next(); }.loop(2){true}{it.object.atom == "Class"}');
        
        return $this;
    }

    public function goToAllParents() {
        $this->addMethod('out("EXTENDS").transform{ g.idx("classes")[["path":it.fullnspath]].next(); }.loop(2){true}{it.object.atom == "Class"}');
        
        return $this;
    }

    public function goToAllChildren() {
        $this->addMethod('transform{root = it.fullnspath; g.idx("atoms")[["atom":"Class"]].filter{ it.getProperty("classTree").findAll{it == root;}.size() > 0; }.toList()}.scatter');
        
        return $this;
    }

    public function goToTraits() {
        $this->addMethod('as("toTraits").out("BLOCK").out("ELEMENT").has("atom", "Use").out("USE").transform{ g.idx("traits")[["path":it.fullnspath]].next(); }.loop("toTraits"){true}{it.object.atom == "Trait"}');
        
        return $this;
    }

    public function hasFunction() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Function"}{it.object.atom == "Function"}.any()}');
        
        return $this;
    }

    public function hasNoFunction() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Function"}{it.object.atom == "Function"}.any() == false}');
        
        return $this;
    }

    public function hasClass() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Class"}{it.object.atom == "Class"}.any()}');
        
        return $this;
    }

    public function hasClassTrait() {
        $this->addMethod('filter{ it.in.loop(1){!(it.object.atom in ["Class", "Trait"])}{it.object.atom in ["Class", "Trait"]}.any()}');
        
        return $this;
    }

    public function hasNoClass() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Class"}{it.object.atom == "Class"}.any() == false}');
        
        return $this;
    }

    public function hasTrait() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Trait"}{it.object.atom == "Trait"}.any()}');
        
        return $this;
    }

    public function hasNoTrait() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Trait"}{it.object.atom == "Trait"}.any() == false}');
        
        return $this;
    }

    public function hasInterface() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Interface"}{it.object.atom == "Interface"}.any()}');
        
        return $this;
    }

    public function hasNoInterface() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Interface"}{it.object.atom == "Interface"}.any() == false}');
        
        return $this;
    }

    public function hasTryCatch() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Try"}{it.object.atom == "Try"}.any()}');
        
        return $this;
    }

    public function hasNotTryCatch() {
        $this->addMethod('filter{ it.in.loop(1){it.object.atom != "Try"}{it.object.atom == "Try"}.any() == false}');
        
        return $this;
    }

    public function goToMethodDefinition() {
        // starting with a staticmethodcall , no support for static, self, parent
        $this->addMethod('sideEffect{methodname = it.out("METHOD").next().code.toLowerCase();}
                .out("CLASS").transform{
                    if (it.code.toLowerCase() == "self") {
                        init = it.in.loop(1){it.object.atom != "Class"}{it.object.atom == "Class"}.next();
                    } else if (it.code.toLowerCase() == "static") {
                        init = it.in.loop(1){it.object.atom != "Class"}{it.object.atom == "Class"}.next();
                    } else  if (it.code.toLowerCase() == "parent") {
                        init = it.in.loop(1){it.object.atom != "Class"}{it.object.atom == "Class"}.next().out("EXTENDS").transform{ g.idx("classes")[["path":it.fullnspath]].next(); }.next();
                    } else {
                        init = g.idx("classes")[["path":it.fullnspath]].next();
                    };

                    find = null;
                    if (init.out("BLOCK").out("ELEMENT").has("atom", "Function").out("NAME").filter{ it.code.toLowerCase() == methodname }.any()) {
                        found = init.out("BLOCK").out("ELEMENT").has("atom", "Function").filter{ it.out("NAME").next().code.toLowerCase() == methodname }.next();
                    } else if (init.out("EXTENDS").any() == false) {
                        found = it;
                    } else {
                        found = init.out("EXTENDS").transform{ g.idx("classes")[["path":it.fullnspath]].next(); }
                            .loop(2){ it.object.out("BLOCK").out("ELEMENT").has("atom", "Function").out("NAME").filter{ it.code.toLowerCase() == methodname }.any() == false}
                                    { it.object.out("BLOCK").out("ELEMENT").has("atom", "Function").out("NAME").filter{ it.code.toLowerCase() == methodname }.any()}
                        .next();
                        
                        if (found == null) { found = it; } else {
                            found = found.out("BLOCK").out("ELEMENT").has("atom", "Function").filter{ it.out("NAME").next().code.toLowerCase() == methodname }.next();
                        }
                    };
                    found;
                }.has("atom", "Function")');
        
        return $this;
    }
    
    public function goToNamespace() {
        $this->addMethod('in.loop(1){!(it.object.atom in ["Namespace", "File"])}{it.object.atom in ["Namespace", "File"]}');
        
        return $this;
    }

    public function fetchContext($variable = null) {
        $this->addMethod('sideEffect{ context = ["Namespace":"Global", "Function":"Global", "Class":"Global"]; it.in.loop(1){true}{it.object.atom in ["Namespace", "Function", "Class"]}.each{ if (it.atom == "Namespace") { context[it.atom] = it.out("NAMESPACE").next().fullcode; } else if (context[it.atom] == "Global") { context[it.atom] = it.out("NAME").next().code; } } }');
        
        if ($variable !== null) {
            $this->addMethod("sideEffect{ $variable = context}");
        }
        return $this;
    }
    
    public function followConnexion( $iterations = 3) {
        //it.rank in x[-2] should be better than !x[-2].intersect([it.rank]).isEmpty() but this isn't working!!
        $this->addMethod(
        <<<GREMLIN

// Loop init
sideEffect{ loops = 1;}

//// LOOP ////
.as('connexion')
.transform{
    if (it.in('METHOD').any() == false) {
        if (g.idx('functions')[['path':it.fullnspath]].any()) {
            g.idx('functions')[['path':it.fullnspath]].next();
        } else {
            it;
        }
    } else if (it.in('METHOD').any()) {  // case of Staticmethodcall or Propertycall
                name = it.code.toLowerCase();
                g.idx('atoms')[['atom':'Class']].out('BLOCK').out('ELEMENT')
                                    .has('atom', 'Function').out('NAME').filter{it.code.toLowerCase() == name }.next();
    } else { it; }
}.in('NAME')
// calculating the path AND obtaining the arguments list
.sideEffect{ while(x.last() >= loops) { x.pop(); x.pop();}; y = it.out('ARGUMENTS').out('ARGUMENT').filter{!x[-2].intersect([it.rank]).isEmpty() }.code.toList(); x += [y];  x += loops;}
// find outgoing function
.out('BLOCK').out.loop(1){true}{it.object.atom in ['Functioncall', 'Staticmethodcall', 'Methodcall'] && (it.object.in('METHOD').any() == false)}
.transform{ if (it.out('METHOD').any()) { it.out('METHOD').next(); } else { it; }}

// filter with arguments that are relayed
.filter{ it.out('ARGUMENTS').out('ARGUMENT').filter{ it.code in x[-2]}.any() }
.sideEffect{ y=[]; it.out('ARGUMENTS').out('ARGUMENT').filter{ it.code in x[-2]}.rank.fill(y); x += [y]; x += loops}

.loop('connexion'){ loops = it.loops; it.loops < $iterations; }{true}
//// LOOP ////

GREMLIN
                        );
        
        return $this;
    }
    
    public function makeVariable($variable) {
        $this->addMethod('sideEffect{ '.$variable.' = "\$" + '.$variable.' }');
        
        return $this;
    }
    
    public function run() {
        $this->analyze();
        $this->prepareQuery();

        $this->execQuery();
        
        return $this->rowCount;
    }
    
    public function getRowCount() {
        return $this->rowCount;
    }

    public function getProcessedCount() {
        return $this->processedCount;
    }

    public function getRawQueryCount() {
        return $this->rawQueryCount;
    }

    public function getQueryCount() {
        return $this->queryCount;
    }

    public abstract function analyze();

    public function printQuery() {
        $this->prepareQuery();
        
        foreach($this->queries as $id => $query) {
            echo $id, ")\n", print_r($query, true), print_r($this->queriesArguments[$id], true);

            krsort($this->queriesArguments[$id]);
            
            foreach($this->queriesArguments[$id] as $name => $value) {
                if (is_array($value)) {
                    $query = str_replace($name, "['".implode("', '", $value)."']", $query);
                } elseif (is_string($value)) {
                    $query = str_replace($name, "'".str_replace('\\', '\\\\', $value)."'", $query);
                } elseif (is_int($value)) {
                    $query = str_replace($name, $value, $query);
                } else {
                    die( 'Cannot process argument of type '.gettype($value)."\n".__METHOD__."\n");
                }
            }
            
            echo $query, "\n\n";
        }
        die();
    }

    public function prepareQuery() {
        // @doc This is when the object is a placeholder for others.
        if (count($this->methods) == 1) { return true; }
        
        array_splice($this->methods, 2, 0, array('as("first")'));
        
        if ($this->methods[1] == 'has("atom", arg1)') {
            $query = implode('.', $this->methods);
            $query = "g.idx('atoms')[['atom':'{$this->arguments['arg1']}']].sideEffect{processed++;}.{$query}";
        } elseif ($this->methods[1] == 'filter{ it.in("ANALYZED").has("code", arg1).any()}') {
            $query = implode('.', $this->methods);
            $query = "g.idx('analyzers')[['analyzer':'".str_replace('\\', '\\\\', $this->arguments['arg1'])."']].out.sideEffect{processed++;}.{$query}";
        } elseif ($this->methods[1] == 'filter{it.atom in arg1}') {
            $q = "z = [];\n";
            foreach($this->arguments['arg1'] as $arg) {
                $q .= 'g.idx("atoms")[["atom":"'.$arg.'"]].fill(z);'."\n";
            }

            unset($this->methods[1]);
            unset($this->arguments['arg1']);

            $query = $q.'z._().sideEffect{processed++;}.'.implode('.', $this->methods);
        } else {
            throw new \Exception('No optimization : gremlin query in analyzer should have use g.V. Fix me!'.get_class($this));
        }
        
        // search what ? All ?
        $query = <<<GREMLIN

processed = 0; total = 0;
m = [:]; gf = [:];
n = [];
{$query}
GREMLIN;
        
        $query .= $this->apply->getGremlin();

    // initializing a new query
        $this->queries[] = $query;
        $this->queriesArguments[] = $this->arguments;

        $this->methods = array();
        $this->arguments = array();
        $this->analyzerIsNot($this->analyzer);
        
        return true;
    }
    
    public function execQuery() {
        if (empty($this->queries)) { return true; }

        // @todo add a test here ?
        foreach($this->queries as $id => $query) {
            $r = $this->query($query, $this->queriesArguments[$id]);
            ++$this->queryCount;
            $r = $r[0];
            if (isset($r->processed)) {
                $this->processedCount += $r->processed;
                $this->rowCount += $r->total;
            } else {
                echo __METHOD__, "\n",
                     $query, "\n",
                     "No result from this query\n";
            } // else means that it is not set, so it's 0. No need for an operation.
        }

        // reset for the next
        $this->queries = array();
        $this->queriesArguments = array();
        
        // @todo multiple results ?
        // @todo store result in the object until reading.
        return $this->rowCount;
    }

    public function toCount() {
        return count($this->toArray());
    }
    
    public function toArray() {
        $queryTemplate = "g.idx('analyzers')[['analyzer':'{$this->analyzerQuoted}']].out";
        $vertices = $this->query($queryTemplate);

        $report = array();
        if (count($vertices) > 0) {
            foreach($vertices as $v) {
                $report[] = $v->fullcode;
            }
        }
        
        return $report;
    }

    public function getArray() {
        $analyzer = str_replace('\\', '\\\\', $this->analyzer);
        if (substr($analyzer, 0, 5) === 'Analyzer\\Files\\') {
            $query = <<<GREMLIN
g.idx('analyzers')[['analyzer':'$analyzer']].out
.has('notCompatibleWithPhpVersion', null)
.has('notCompatibleWithPhpConfiguration', null)
.as('fullcode').as('line').as('filename').select{it.fullcode}{it.line}{it.fullcode}
GREMLIN;
        } else {
            $query = <<<GREMLIN
g.idx('analyzers')[['analyzer':'$analyzer']].out
.has('notCompatibleWithPhpVersion', null)
.has('notCompatibleWithPhpConfiguration', null)
.as('fullcode').in.loop(1){ it.object.token != 'T_FILENAME'}.as('file').back('fullcode').as('line').select{it.fullcode}{it.line}{it.fullcode}
GREMLIN;
        }
        $vertices = $this->query($query);

        $analyzer = $this->analyzer;
        $report = array();
        if (count($vertices) > 0) {
            foreach($vertices as $v) {
                if (!isset($v->file)) {
                    echo "Error in getArray() : Couldn't find the file\n$query\n",
                         get_class($this),
                         "\n",
                         print_r($v, true);
                    die();
                }
                $report[] = array('code' => $v->fullcode,
                                  'file' => $v->file,
                                  'line' => $v->line,
                                  'desc' => $this->description->getName(),
                                  'clearphp' => $this->description->getClearPHP(),
                                  );
            }
        }
        
        return $report;
    }

    public function toCountedArray($load = 'it.fullcode') {
        $analyzer = str_replace('\\', '\\\\', $this->analyzer);
        $queryTemplate = "m = [:]; g.idx('analyzers')[['analyzer':'".$analyzer."']].out.groupCount(m){".$load.'}.cap';
        $vertices = $this->query($queryTemplate);

        $report = array();
        if (count($vertices) > 0) {
            foreach($vertices[0][0] as $k => $v) {
                $report[$k] = $v;
            }
        }
        
        return $report;
    }
    
    protected function loadIni($file, $index = null) {
        $config = \Config::factory();
        $fullpath = $config->dir_root.'/data/'.$file;
        
        if (!file_exists($fullpath)) {
            return null;
        }

        $iniFile = parse_ini_file($fullpath);
        
        if ($index != null && isset($iniFile[$index])) {
            return $iniFile[$index];
        }
        
        return $iniFile;
    }

    protected function loadJson($file) {
        $config = \Config::factory();
        $fullpath = $config->dir_root.'/data/'.$file;

        if (!file_exists($fullpath)) {
            return null;
        }

        $jsonFile = json_decode(file_get_contents($fullpath));
        
        return $jsonFile;
    }
    
    public static function listAnalyzers() {
        self::initDocs();
        return self::$docs->listAllAnalyzer();
    }

    public function isRun() {
        $analyzer = str_replace('\\', '\\\\', $this->analyzer);
        $queryTemplate = "g.idx('analyzers')[['analyzer':'".$analyzer."']].any()";
        $vertices = $this->query($queryTemplate);

        return $vertices[0][0] == 1;
    }
    
    public function hasResults() {
        return (bool) ($this->getResultsCount() > 0);
    }

    public function getResultsCount() {
        $analyzer = str_replace('\\', '\\\\', $this->analyzer);
        $queryTemplate = <<<GREMLIN
g.idx('analyzers')[['analyzer':'$analyzer']].out
.has('notCompatibleWithPhpVersion', null)
.has('notCompatibleWithPhpConfiguration', null)
.count();

GREMLIN;
        $vertices = $this->query($queryTemplate);
        
        $return = (int) $vertices[0];
        if ($return > 0) {
            return $return;
        }
        
        $queryTemplate = <<<GREMLIN
g.idx('analyzers')[['analyzer':'$analyzer']].out
.transform{ ['versionCompatible':it.notCompatibleWithPhpVersion,
             'configurationCompatible': it.notCompatibleWithPhpConfiguration]};

GREMLIN;
        $vertices = $this->query($queryTemplate);
        
        if (empty($vertices[0])) { // really no results
            return 0;
        } elseif (isset($vertices[0]->versionCompatible)) {
            return self::VERSION_INCOMPATIBLE;
        } elseif (isset($vertices[0]->configurationCompatible)) {
            return self::CONFIGURATION_INCOMPATIBLE;
        } else {
            // Error
            return 0;
        }
    }
    
    public function getSeverity() {
        if (Analyzer::$docs === null) {
            Analyzer::$docs = new Docs(dirname(dirname(dirname(__FILE__))).'/data/analyzers.sqlite');
        }
        
        return Analyzer::$docs->getSeverity($this->analyzer);
    }

    public function getFileList() {
        $analyzer = str_replace('\\', '\\\\', $this->analyzer);
        $query = "m=[:]; g.idx('analyzers')[['analyzer':'".$analyzer."']].out('ANALYZED').in.loop(1){true}{it.object.atom == 'File'}.groupCount(m){it.fullcode}.iterate(); m;";
        $vertices = $this->query($query);
        
        $return = array();
        foreach($vertices as $k => $v) {
            $return[$k] = (array) $v;
        }
        
        return $return;
    }

    public function getVendors() {
        if (Analyzer::$docs === null) {
            Analyzer::$docs = new Docs(dirname(dirname(dirname(__FILE__))).'/data/analyzers.sqlite');
        }
        
        return Analyzer::$docs->getVendors();
    }

    public function getTimeToFix() {
        return $this->timeToFix;
    }

    public function getPhpversion() {
        return $this->phpVersion;
    }

    public function getphpConfiguration() {
        return $this->phpConfiguration;
    }
    
    public function makeFullNsPath($functions) {
        if (is_string($functions)) {
            $r = strtolower($functions);
            if (isset($r[0]) && $r[0] != "\\") {
                $r = "\\". $r;
            }
        } else {
            $r = array_map(function ($x) {
                $r = strtolower($x);
                if (isset($r[0]) && $r[0] != "\\") {
                    $r = "\\". $r;
                }
                return $r;
            },  $functions);
        }
        return $r;
    }
    
    private function tolowercase(&$code) {
        if (is_array($code)) {
            foreach($code as $k => &$v) {
                $v = strtolower($v);
            }
            unset($v);
        } else {
            $code = strtolower($code);
        }
    }
}
?>
