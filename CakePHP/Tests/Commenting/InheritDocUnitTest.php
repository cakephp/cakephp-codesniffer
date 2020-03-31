<?php

namespace CakePHP\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class InheritDocUnitTest extends AbstractSniffUnitTest
{
    /**
     * @inheritDoc
     */
    public function getErrorList($testFile = '')
    {
        switch ($testFile) {
            case 'InheritDocUnitTest.1.inc':
                return [
                    '24' => 1,
                    '29' => 1,
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
            case 'InheritDocUnitTest.1.inc':
                return [
                    '12' => 1,
                ];

            default:
                return [];
        }
    }
}
