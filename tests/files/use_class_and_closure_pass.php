<?php

class TraitUser {

	use FunctionsTrait;

	public function doThing(callable $callback) {
		$visitor = function($expression) use (&$visitor, $callback) {
			echo 'It works';
		};
		$visitor($this);
	}

}
