<?php

$expected     = array('posix_access($file, POSIX_R_OK | POSIX_W_OK)',
                      'posix_get_last_error()', 
                      'posix_strerror($error)');

$expected_not = array();

?>