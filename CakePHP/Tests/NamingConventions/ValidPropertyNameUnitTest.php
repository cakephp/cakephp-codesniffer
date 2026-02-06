<?php

namespace CakePHP\Tests\NamingConventions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffTestCase;

class ValidPropertyNameUnitTest extends AbstractSniffTestCase
{
    /**
     * @inheritDoc
     */
    public function getErrorList(): array
    {
        return [
            9 => 1,  // public $_publicUnderscore
            13 => 1, // protected $_protectedUnderscore
            17 => 1, // private $_privateUnderscore
            21 => 1, // protected static $_protectedStatic
            23 => 1, // private static $_privateStatic
            30 => 1, // public $_publicUnderscore (trait)
            34 => 1, // protected $_protectedUnderscore (trait)
            38 => 1, // private $_privateUnderscore (trait)
        ];
    }

    /**
     * @inheritDoc
     */
    public function getWarningList(): array
    {
        return [];
    }
}
