<?php

if (1) 2;

if (3) ; else 4;

if (4) :
    $ifnoblock++;
elseif (5) :
    $elseifnoblock++;
elseif (6) :
    $elseifnoblock++;
elseif (8) :
    $elseifnoblock++;
else :
    $elsenoblock++;
endif;

foreach($a2 as $b2) :
    $foreachnoblock++;
endforeach;
    
for(7;;) : $fornoblock++; endfor;

while (7) : $whilenoblock++; endwhile;

?>