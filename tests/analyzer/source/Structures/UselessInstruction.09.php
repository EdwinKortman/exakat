<?php
$a = array(1);

$b = array_merge($a);
$c = array_merge($a, $b);
$d = array_merge(...$c); // Not useless!!

?>