<?php

$expected     = array('$GLOBALS[\'usedOnce1\']',
                      '$usedOnce2',
                      '$GLOBALS[\'usedTwicegG\']', 
                      '$GLOBALS[\'usedTwiceGg\']',
                      '$usedTwicegG', 
                      '$usedTwiceGg',
);

$expected_not = array('$GLOBALS[\'unusedGlobal2\']',
                      '$unusedGlobal1',
                      '$unusedGLobal2',
                      '$unusedGlobal',
                      '$GLOBALS[\'usedTwicegG\']', 
                      '$GLOBALS[\'usedTwiceGg\']'
);

?>