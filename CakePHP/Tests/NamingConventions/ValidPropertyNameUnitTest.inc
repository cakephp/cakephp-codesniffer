<?php

namespace Beakman;

class PropertyNames
{
    public string $publicProperty = '';

    public string $_publicUnderscore = ''; // error

    protected string $protectedProperty = '';

    protected string $_protectedUnderscore = ''; // error

    private string $privateProperty = '';

    private string $_privateUnderscore = ''; // error

    public static string $publicStatic = '';

    protected static string $_protectedStatic = ''; // error

    private static string $_privateStatic = ''; // error
}

trait PropertyNamesTrait
{
    public string $publicProperty = '';

    public string $_publicUnderscore = ''; // error

    protected string $protectedProperty = '';

    protected string $_protectedUnderscore = ''; // error

    private string $privateProperty = '';

    private string $_privateUnderscore = ''; // error
}
