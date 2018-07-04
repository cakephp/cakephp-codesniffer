<?php

class ReturnTypeDeclaration {

/**
 * A function
 *
 * @return void
 */
	public function returnTypeVoid() : void {
	}

/**
 * A nullable function
 *
 * @return string|null
 */
	public function returnTypeNull() : ?string {
	}

/**
 * A function with param
 *
 * @param int $firstParam a first param
 * @param string $secondParam a second param
 * @return int
 */
	public function withParam(int $firstParam, string $secondParam) : int {
	}

}
