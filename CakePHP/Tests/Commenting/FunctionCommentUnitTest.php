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
        ];
    }

    /**
     * @inheritDoc
     */
    public function getWarningList()
    {
        return [
            37 => 1,
        ];
    }
}
