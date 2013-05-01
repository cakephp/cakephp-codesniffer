<?php

/**
 * FuelPHP_Sniffs_NamingConventions_LowerCaseFileNameSniff.
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

/**
 * FuelPHP_Sniffs_NamingConventions_LowerCaseFileNameSniff.
 *
 * Throws errors if file names contain uppercase.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Eric VILLARD <dev@eviweb.fr>
 * @copyright 2012 Eric VILLARD <dev@eviweb.fr>
 * @license   http://opensource.org/licenses/MIT MIT License
 * @version   Release: 1.0.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class FuelPHP_Sniffs_NamingConventions_LowerCaseFileNameSniff 
    implements PHP_CodeSniffer_Sniff
{

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
        'PHP',
        'JS',
        'CSS',
    );

    /**
     * last file name checked
     * 
     * @var string
     */
    protected static $lastfile = '';

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_WHITESPACE);
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile All the tokens found in the document.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $filename = $phpcsFile->getFilename();
        if (static::$lastfile === $filename) {
            return;
        }
        static::$lastfile = $filename;
        $filename = basename($filename);
        if ($filename !== strtolower($filename)) {
            $error = 'File names must be all lower case;
                no upper case is allowed';
            $phpcsFile->addError($error, $stackPtr, 'UpperCaseInFileName');
        }
    }
}

?>