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
    const SOME_bad_CONST = 'const';

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
        $const = Test::SOME_CONST;
        $const = Cake\Test::SOME_CONST;
        $test = <<<EOT
EOT;
    }
}

define('MY_CONSTANT', 'const');