<?php
namespace Cake;

/**
 * A class for constants
 */
class ConstantNames
{
    /**
     * @var string
     */
    const SOME_CONST = 'const';

    /**
     * A function
     *
     * @return void
     */
    public function getClass()
    {
        $className = static::class;
        $const = self::SOME_CONST;
        $constTwo = static::SOME_CONST;
    }
}

define('MY_CONSTANT', 'const');
