<?php

namespace CakePHP\Tests\NamingConventions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffTestCase;

class ValidEnumNameUnitTest extends AbstractSniffTestCase
{
    /**
     * @inheritDoc
     */
    public function getErrorList()
    {
        return [
            2 => 1,
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
