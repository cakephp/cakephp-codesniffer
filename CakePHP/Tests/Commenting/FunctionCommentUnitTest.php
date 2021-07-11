<?php

namespace CakePHP\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class FunctionCommentUnitTest extends AbstractSniffUnitTest
{
    /**
     * @inheritDoc
     */
    public function getErrorList($testFile = '')
    {
        switch ($testFile) {
            case 'FunctionCommentUnitTest.1.inc':
                return [
                    15 => 1,
                    20 => 1,
                    34 => 1,
                    41 => 1,
                    50 => 1,
                    58 => 1,
                ];

            default:
                return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function getWarningList($testFile = '')
    {
        switch ($testFile) {
            case 'FunctionCommentUnitTest.1.inc':
                return [
                    93 => 1,
                ];

            case 'FunctionCommentUnitTest.2.inc':
                return [
                    16 => 1,
                    17 => 1,
                    18 => 1,
                ];

            default:
                return [];
        }
    }
}
