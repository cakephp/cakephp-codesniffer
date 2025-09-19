<?php

namespace CakePHP\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffTestCase;

class TypeHintUnitTest extends AbstractSniffTestCase
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
                    12 => 1,
                    15 => 1,
                    27 => 1,
                    37 => 1,
                    42 => 1,
                ];

            default:
                return [];
        }
    }
}
