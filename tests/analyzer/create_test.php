<?php
    $args = $argv;
    
    if (!isset($args[1])) {
        print "Usage : create_test.php Testname\n ";
        die();
    }
    $test = $args[1];
    
    if (strpos($test, '/') === false) {
        print "The test should look like 'X/Y'. Aborting\n";
        die();
    }
    
    list($dir, $test, $test2) = explode('/', $test);
    if ($dir === 'Test') {
        $dir = $test;
        $test = $test2;
    }
    
    if (substr($test, -4) == '.php') {
        $test = substr($test, 0, -4);
        print "Dropping extension .php from the test name. Now using '$test'\n";
    }

    if (!file_exists(dirname(dirname(__DIR__)).'/library/Exakat/Analyzer/'.$dir)) {
        $groups = array_map('basename', glob(dirname(dirname(__DIR__)).'/library/Exakat/Analyzer/*' , GLOB_ONLYDIR));
        $closest = closest_string($dir, $groups);
        print "No such analyzer group '$dir'. Did you mean '$closest' ? \nChoose among : ".join(', ', $groups);
        
        print ". Aborting.\n";
        die();
    }

    if (!file_exists(dirname(dirname(__DIR__)).'/library/Exakat/Analyzer/'.$dir.'/'.$test.'.php')) {
        $groups = array_map( function ($name) { return substr(basename($name), 0, -4); }, 
                             glob(dirname(dirname(__DIR__)).'/library/Exakat/Analyzer/'.$dir.'/*'));
        $closest = closest_string($dir, $groups);
        print "No such analyzer '{$dir}/$test'. Did you mean '$closest' ? \nChoose among : ".join(', ', $groups);
        
        print ". Aborting.\n";
        die();
    }
    
    // restore $test value
    $test_ = $dir.'_'.$test;
    $test = $dir.'/'.$test;
    $files = glob('source/'.$test.'.*.php');
    sort($files);
    $last = array_pop($files);
    $number = intval(str_replace(array($test, '.php', '.', 'source/'), '', $last));
    
    if ($number + 1 == 100) { 
        print "Too many tests for $test : reaching 100 of them. Aborting\n";
        die();
    }
    $next = substr("00".($number + 1), -2);

    if (file_exists('Test/'.$test.'.php')) {
        $code = file_get_contents('Test/'.$test.'.php');
    } else {
        copy('Test/Skeleton.php', 'Test/'.$test.'.php');
        $code = file_get_contents('Test/'.$test.'.php');
        
        $code = str_replace('Skeleton', $test_, $code);
    }

    $code = substr($code, 0, -4)."    public function test$test_$next()  { \$this->generic_test('$test.$next'); }
".substr($code, -4);
    $count = $next + 0;
    $code = preg_replace('#/\* \d+ methods \*/#is', '/* '.$count.' methods */', $code);

    file_put_contents('Test/'.$test.'.php', $code);

    if (in_array('-d', $argv)) {
        print "Creating directory file\n";
        mkdir('./source/'.$test.'.'.$next.'.php',0755);
        file_put_contents('./source/'.$test.'.'.$next.'.php/test.01.php', "<?php

?>");
        shell_exec('bbedit ./source/'.$test.'.'.$next.'.php/test.01.php');
    } else {
        print "Creating test file\n";
        file_put_contents('./source/'.$test.'.'.$next.'.php', "<?php

?>");
        shell_exec('bbedit ./source/'.$test.'.'.$next.'.php');
    }
    
    file_put_contents('./exp/'.$test.'.'.$next.'.php', "<?php

\$expected     = array();

\$expected_not = array();

?>");

    shell_exec('bbedit ./exp/'.$test.'.'.$next.'.php');
    
    echo "New test number : $next\n",
         "Run the tests with     phpunit Test/$test.php\n",
         "Run the tests with     phpunit --filter=$next Test/$test.php\n",
         "Run manual test with   php manualTest.php --filter=$next Test/$test.php\n",
         "\n";

    function closest_string($string, $array) {
        $shortest = -1;

        $closest = '';
        foreach ($array as $a) {
            $lev = levenshtein($string, $a);

            if ($lev == 0) {
                $closest = $a;
                $shortest = 0;
                break;
            }

            if ($lev <= $shortest || $shortest < 0) {
                // set the closest match, and shortest distance
                $closest  = $a;
                $shortest = $lev;
            }
        }
        
        return $closest;
    }
?>