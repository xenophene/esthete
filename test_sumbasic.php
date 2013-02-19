<?php

include 'SumBasic.php';
$s = new SumBasic('I have this test. Will this test work? I have my doubts.');
echo $s->run(2);