<?php

namespace CakePHP\Tests\ControlStructures;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffTestCase;

class ElseIfDeclarationUnitTest extends AbstractSniffTestCase
{
    /**
     * @inheritDoc
     */
    public function getErrorList()
    {
        return [
            4 => 1,
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
