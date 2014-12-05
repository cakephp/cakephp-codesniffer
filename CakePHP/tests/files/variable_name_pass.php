<?php
namespace Beakman;

class VariablenamePass
{
    public $passing;

    public $passingPublic = 'defined';

    protected $underScoredStart = 'OK';

    protected $underScored;

    private $doubleUnderscore = 'applications';

    public static $publicStatic = true;

    protected static $protectedStatic = true;

    private static $privateStatic = true;

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
}
