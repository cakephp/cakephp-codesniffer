<?php
namespace Beakman;

class FunctionNames
{
    /**
     * [setVariables description]
     *
     * @return void
     */
    public function setVariables()
    {
        $this->passingPublic = 'changed';
        $this->underscored = 'has value now';
        $this->doubleUnderscore = 'not recommended';
    }

    /**
     * [setStatics description]
     *
     * @return void
     */
    public static function setStatics()
    {
        self::$publicStatic = true;
        self::$protectedStatic = true;
        self::$privateStatic = true;
    }

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
