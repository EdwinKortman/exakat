<?php

$expected     = array('unset($value)',
                      'unset($valuep)',
                      'unset($valuep->property)',
                      'unset($valuep2)',
                      'unset($valuep2->property)',
                      'unset($valuek)',
                      'unset($theStatic)',
                      'unset($theGLobal)',
                      'unset($argByReference)',
                      'unset($argByValue)'
);

$expected_not = array('unset($valuep2->property->property2)',
                      'unset($valuep2->property->property2)',);

?>