<?php

namespace CakePHP\Tests\NamingConventions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffTestCase;

class ValidFunctionNameUnitTest extends AbstractSniffTestCase
{
    /**
     * @inheritDoc
     */
    public function getErrorList(): array
    {
        return [
            6 => 1,   // public function _forbidden
            30 => 1,  // protected function _someFunc
            103 => 1, // public function _forbidden (interface)
            112 => 1, // public function _forbidden (trait)
            136 => 1, // protected function _someFunc (trait)
        ];
    }

    /**
     * @inheritDoc
     */
    public function getWarningList(): array
    {
        return [];
    }
}
