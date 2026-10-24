<?php

namespace CakePHP\Tests\NamingConventions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffTestCase;

class ValidEnumNameUnitTest extends AbstractSniffTestCase
{
    /**
     * @inheritDoc
     */
    public function getErrorList($testFile = '')
    {
        switch ($testFile) {
            case 'ValidEnumNameUnitTest.1.inc':
                return [
                    2 => 1,
                ];

            case 'ValidEnumNameUnitTest.2.inc':
                // No errors - enums in Enum namespace don't need suffix
                return [];

            default:
                return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function getWarningList($testFile = '')
    {
        return [];
    }
}
