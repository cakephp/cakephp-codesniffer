<?php

namespace CakePHP;

use Other\Crap;
use Other\Error as OtherError;

class Throws
{

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
