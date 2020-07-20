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
            12 => 1,
            13 => 1,
            23 => 1,
            24 => 1,
            34 => 1,
            35 => 1,
            90 => 1,
            97 => 1,
            162 => 1,
            171 => 1,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getWarningList()
    {
        return [
            14 => 1,
            31 => 2,
            45 => 1,
            124 => 1,
            129 => 1,
            137 => 1,
            138 => 1,
            145 => 1,
            146 => 1,
            155 => 1,
            161 => 1,
            226 => 1,
        ];
    }
}
