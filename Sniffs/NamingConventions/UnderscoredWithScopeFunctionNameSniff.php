<?php
/**
 * FuelPHP_Sniffs_NamingConventions_UnderscoredWithScopeFunctionNameSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Eric VILLARD <dev@eviweb.fr>
 * @copyright 2012 Eric VILLARD <dev@eviweb.fr>
 * @license   http://opensource.org/licenses/MIT MIT License
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if (class_exists('PHP_CodeSniffer_Standards_AbstractScopeSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception(
        'Class PHP_CodeSniffer_Standards_AbstractScopeSniff not found'
    );
}

/**
 * FuelPHP_Sniffs_NamingConventions_UnderscoredWithScopeFunctionNameSniff.
 *
 * Ensures method names use underscore format and their visibility scope is defined.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Eric VILLARD <dev@eviweb.fr>
 * @copyright 2012 Eric VILLARD <dev@eviweb.fr>
 * @license   http://opensource.org/licenses/MIT MIT License
 * @version   Release: 1.0.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class FuelPHP_Sniffs_NamingConventions_UnderscoredWithScopeFunctionNameSniff
    extends PHP_CodeSniffer_Standards_AbstractScopeSniff
{
    /**
     * A list of all PHP magic methods.
     *
     * @var array
     */
    protected $magicMethods = array(
                               'construct',
                               'destruct',
                               'call',
                               'callStatic',
                               'get',
                               'set',
                               'isset',
                               'unset',
                               'sleep',
                               'wakeup',
                               'toString',
                               'set_state',
                               'clone',
                               'invoke',
                               'call',
                              );
        
    /**
     * A list of all PHP magic functions.
     *
     * @var array
     */
    protected $magicFunctions = array('autoload');

    /**
     * Constructs a FuelPHP_Sniffs_NamingConventions_LowerCaseUnderscoreFunctionNameSniff.
     */
    public function __construct()
    {
        parent::__construct(
            array(T_CLASS, T_INTERFACE, T_TRAIT), 
            array(T_FUNCTION), 
            true
        );
    }//end __construct()

    /**
     * Processes the tokens within the scope.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
     * @param int                  $stackPtr  The position where this token was
     *                                        found.
     * @param int                  $currScope The position of the current scope.
     *
     * @return void
     */
    protected function processTokenWithinScope(
        PHP_CodeSniffer_File $phpcsFile, 
        $stackPtr, 
        $currScope
    ) {
        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        if ($methodName === null) {
            // Ignore closures.
            return;
        }

        $className = $phpcsFile->getDeclarationName($currScope);
        $errorData = array($className.'::'.$methodName);

        // Is this a magic method. i.e., is prefixed with "__" ?
        if (preg_match('|^__|', $methodName) !== 0) {
            $magicPart = substr($methodName, 2);
            if (in_array($magicPart, $this->magicMethods) === false) {
                 $error = 'Method name "%s" is invalid; 
                     only PHP magic methods should be prefixed with a 
                     double underscore';
                 $phpcsFile->addError(
                     $error,
                     $stackPtr,
                     'MethodDoubleUnderscore', 
                     $errorData
                 );
            }

            return;
        }

        // PHP4 constructors are allowed to break our rules.
        if ($methodName === $className) {
            return;
        }

        // PHP4 destructors are allowed to break our rules.
        if ($methodName === '_'.$className) {
            return;
        }		

        $methodProps = $phpcsFile->getMethodProperties($stackPtr);
        if ($methodProps['scope_specified'] !== true) {
            $error = 'Method visibility scope must be specified for "%s".';
            $phpcsFile->addError($error, $stackPtr, 'VisibilityScope', $errorData);
        }

        // check underscore format and visibility scope
        if (static::isUnderscoreName($methodName) === false) {
            if ($methodProps['scope_specified'] === true) {
                $error = '%s method name "%s" does not use underscore format. 
                    Upper case forbidden.';
                $data  = array(
                          ucfirst($methodProps['scope']),
                          $errorData[0],
                         );
                $phpcsFile->addError($error, $stackPtr, 'ScopeNotUnderscore', $data);
            } else {
                $error = 'Method name "%s" does not use underscore format.
                     Upper case forbidden.';
                $phpcsFile->addError(
                    $error,
                    $stackPtr,
                    'NotUnderscore',
                    $errorData
                );
            }
            return;
        }

    }//end processTokenWithinScope()


    /**
     * Processes the tokens outside the scope.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
     * @param int                  $stackPtr  The position where this token was
     *                                        found.
     *
     * @return void
     */
    protected function processTokenOutsideScope(
        PHP_CodeSniffer_File $phpcsFile, $stackPtr
    ) {
        $functionName = $phpcsFile->getDeclarationName($stackPtr);
        if ($functionName === null) {
            // Ignore closures.
            return;
        }

        $errorData = array($functionName);

        // Is this a magic function. IE. is prefixed with "__".
        if (preg_match('|^__|', $functionName) !== 0) {
            $magicPart = substr($functionName, 2);
            if (in_array($magicPart, $this->magicFunctions) === false) {
                 $error = 'Function name "%s" is invalid; only PHP magic methods 
                     should be prefixed with a double underscore';
                 $phpcsFile->addError(
                     $error,
                     $stackPtr,
                     'FunctionDoubleUnderscore',
                     $errorData
                 );
            }

            return;
        }

        if (static::isUnderscoreName($functionName) === false) {
            $error = 'Function name "%s" does not use underscore format. 
                Upper case forbidden.';
            $phpcsFile->addError($error, $stackPtr, 'NotUnderscore', $errorData);
        }


    }//end processTokenOutsideScope()

    /**
     * Returns true if the specified string is in the underscore caps format.
     *
     * @param string $string The string to verify.
     *
     * @return boolean
     */
    public static function isUnderscoreName($string)
    {
        // If there are space in the name, it can't be valid.
        if (strpos($string, ' ') !== false) {
            return false;
        }

        if ($string !== strtolower($string)) {
            return false;
        }

        $validName = true;
        $nameBits  = explode('_', $string);

        foreach ($nameBits as $bit) {
            if ($bit === '') {
                continue;
            }

            if ($bit{0} !== strtolower($bit{0})) {
                $validName = false;
                break;
            }
        }

        return $validName;

    }//end isUnderscoreName()

}//end class

?>