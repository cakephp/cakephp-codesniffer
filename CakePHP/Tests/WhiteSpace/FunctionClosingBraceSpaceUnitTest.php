<?php

namespace CakePHP\Tests\WhiteSpace;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffTestCase;

class FunctionClosingBraceSpaceUnitTest extends AbstractSniffTestCase
{
    /**
     * @inheritDoc
     */
    public function getErrorList()
    {
        return [
            3 => 1,
            6 => 1,
            10 => 1,
            25 => 1,
            28 => 1,
            30 => 1,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getWarningList()
    {
        return [];
    }
}
