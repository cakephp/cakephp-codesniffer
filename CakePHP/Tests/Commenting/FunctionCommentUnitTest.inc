<?php
namespace Beakman;

use Other\Crap;
use Other\Error as OtherError;

class Foo
{
    /**
     * Some sentence.
     *
     * @param integer $param Some Param.
     * @param boolean $otherParam Some Other Param.
     * @return string Something.
     */
    public function bar($param, $otherParam)
    {
    }

    /**
     * Some sentence.
     *
     * @return void
     */
    public function baz()
    {
        return 'string';
    }

    /**
     * [doThing description]
     *
     * @param string $foo Foo foo foo.
     * @return void
     */
    public function doThing($foo)
    {
    }

    /**
     * Test throws
     *
     * @throws Exception An expection happened.
     * @throws CakePHP\Boom A boom went off.
     * @throws CakePHP\Error\Boom Oh, shucks, another boom.
     * @throws Other\Crap Oh boy.
     * @throws Other\Error\Issue A missing tissue for your PSR-2 issues.
     * @return void
     */
    public function test()
    {
        switch ($a) {
            case 1:
                throw new Boom();
            case 2:
                throw new Error\Boom();
            case 3:
                throw new OtherError\Issue();
            case 4:
                throw new Crap();
            default:
                throw new \Exception();
        }
    }
}