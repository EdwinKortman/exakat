<?php

$expected     = array('substr($a, 0, 1) == \'\b\'', 
                      'substr($a, 0, 1) == \'bc\'',
                     );

$expected_not = array('\'\\\'',
                      '\'\r\'',
                      '\'\032\'',
                      '\'\u{00002}\'',
                      '\'\xaa\'',
                      '\'\x66\'',
                      '\'\p{Cc}\'',
                      '\'\P{Cc}\'',
                      '\'\XCc\'',
                      '\'bc\'',
                      '\'\b\'',
                     );

?>