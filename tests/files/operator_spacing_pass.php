<?php
$foo = -1;

switch($foo) {
	case -1:
	break;
}
$bar = isset($foo) ? -2 : 0;
$foo = isset($bar) ? 0 : -2;
$ten = 10 * 2;
$ten = -10 * -2;
$ten = -10 / -2;
$ten = -10 + -2;
$ten = -10 - -2;
$dec = -0.001;
$dec = -1;
