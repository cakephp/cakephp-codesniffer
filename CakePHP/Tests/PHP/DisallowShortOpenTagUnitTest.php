<?php

namespace CakePHP\Tests\PHP;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffTestCase;

class DisallowShortOpenTagUnitTest extends AbstractSniffTestCase
{
    /**
     * @inheritDoc
     */
    public function getErrorList()
    {
        return [
            8 => 1,
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
