<?php

namespace CakePHP\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class TypeHintUnitTest extends AbstractSniffUnitTest
{
    /**
     * @inheritDoc
     */
    public function getErrorList($testFile = '')
    {
        switch ($testFile) {
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
            case 'TypeHintUnitTest.1.inc':
                return [
                    6 => 1,
                    9 => 1,
                    22 => 1,
                    23 => 1,
                    24 => 1,
                    25 => 1,
                    37 => 1,
                    40 => 1,
                ];

            default:
                return [];
        }
    }
}
