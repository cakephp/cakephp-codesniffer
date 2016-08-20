<?php
namespace Beakman;

class VariableNames
{
    protected $_someProp;

    public $passing;

    public $passingPublic = 'defined';

    protected $underScoredStart = 'OK';

    protected $underScored;

    private $doubleUnderscore = 'applications';

    public static $publicStatic = true;

    protected static $protectedStatic = true;

    private static $privateStatic = true;
}
