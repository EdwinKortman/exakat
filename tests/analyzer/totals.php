<?php

$files = glob('./Test/*.php');

print count($files)." Tests\n";

$res = shell_exec('grep " methods " Test/*.php | grep -v Skeleton');
$numbers = explode("\n", trim($res));

$numbers = array_map(function ($x) { preg_match('/ (\d+) method/', $x, $r); return $r[1]; }, $numbers);

print array_sum($numbers)." methods\n";
print floor(array_sum($numbers) / count($numbers))." on average\n";
print min($numbers)." minimum\n";
print max($numbers)." maximum\n";


// unfinished tests
$total = 0;
$files = glob('exp/*.php');
foreach($files as $file) {
    include($file);
    
    if (empty($expected) && empty($expected_not)) {
        ++$total;
        print "Empty tests : $file\n";
    }
}
print "total unfinished tests : $total\n";

$total = 0;
$files = glob('source/*.php');
foreach($files as $file) {
    if (filesize($file) < 15) {
        ++$total;
        print "Empty tests : $file\n";
    }
}
print "total unfinished tests : $total\n";

?>