<?php
namespace Cake;

class ClassWithUndercore extends Object
{

    protected $_someProp;

    /**
     * [_someFunc description]
     *
     * @return void
     */
    protected function _someFunc()
    {
        // code here
    }

    /**
     * [noUnderscorePrefix description]
     *
     * @return void
     */
    protected function noUnderscorePrefix()
    {
        // code here
    }
}
