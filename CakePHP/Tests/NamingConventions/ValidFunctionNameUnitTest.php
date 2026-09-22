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
            6 => 1,
            30 => 1,
            103 => 1,
            112 => 1,
            136 => 1,
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
