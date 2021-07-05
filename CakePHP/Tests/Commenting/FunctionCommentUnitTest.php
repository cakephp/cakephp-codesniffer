<?php

namespace CakePHP\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class FunctionCommentUnitTest extends AbstractSniffUnitTest
{
    /**
     * @inheritDoc
     */
    public function getErrorList()
    {
        return [
            15 => 1,
            20 => 1,
            34 => 1,
            41 => 1,
            50 => 1,
            58 => 1,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getWarningList()
    {
        return [
            93 => 1,
        ];
    }
}
