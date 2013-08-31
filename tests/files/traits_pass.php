<?php

use Cake\Last;
use Cake\More;

class Foo {

	use BarTrait;
	use FirstTrait {
		config as protected _config;
	}

}
