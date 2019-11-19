<?php

for ($i = 0; $i < 10; $i++) {
    echo 'hello';
}

if ($i < 10) {
    echo 'hello2';
} elseif ($i > 100) {
    echo 'i > 100';
}

if($abc == true)
        echo 'hello';

// PSR12.ControlStructures.ControlStructureSpacing.FirstExpressionLine
$foo = true;
$bar = true;
$bat = true;
if (
    $foo
    && $bar
    && $bat
) {
    echo 'Hello';
}
