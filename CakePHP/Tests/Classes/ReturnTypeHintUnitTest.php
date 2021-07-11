<?php

namespace CakePHP\Tests\Classes;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class ReturnTypeHintUnitTest extends AbstractSniffUnitTest
{
    /**
     * @inheritDoc
     */
    public function getErrorList()
    {
        return [
            16 => 1,
            23 => 1,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getWarningList()
    {
        return [
        ];
    }
}
