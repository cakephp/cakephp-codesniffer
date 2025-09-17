<?php

namespace CakePHP\Tests\WhiteSpace;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffTestCase;

class FunctionCallSpacingUnitTest extends AbstractSniffTestCase
{
    /**
     * @inheritDoc
     */
    public function getErrorList()
    {
        return [
            2 => 1,
            3 => 1,
            4 => 1,
            5 => 1,
            6 => 1,
            7 => 1,
            8 => 1,
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
