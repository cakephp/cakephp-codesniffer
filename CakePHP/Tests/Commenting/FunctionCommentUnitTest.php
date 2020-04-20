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
            104 => 1,
            112 => 1,
            178 => 1,
            187 => 1,
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
            140 => 1,
            145 => 1,
            153 => 1,
            154 => 1,
            161 => 1,
            162 => 1,
            171 => 1,
            177 => 1,
            242 => 1,
        ];
    }
}
