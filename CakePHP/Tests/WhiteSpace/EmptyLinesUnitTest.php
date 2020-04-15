<?php

namespace CakePHP\Tests\WhiteSpace;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class EmptyLinesUnitTest extends AbstractSniffUnitTest
{
    /**
     * @inheritDoc
     */
    public function getErrorList()
    {
        return [
            8 => 1,
            21 => 1,
            42 => 1,
            43 => 1,
            47 => 1,
            48 => 1,
            49 => 1,
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
