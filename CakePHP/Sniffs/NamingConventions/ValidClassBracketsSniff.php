<?php
/**
 * PHP Version 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 * @since         CakePHP CodeSniffer 0.1.12
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Ensures curly brackets are on the same line as the Class declaration
 *
 */
namespace CakePHP\Sniffs\NamingConventions;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class ValidClassBracketsSniff implements Sniff
{

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
    public function register()
    {
        return array(T_CLASS);
    }

/**
 * Processes this test, when one of its tokens is encountered.
 *
 * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
 * @param int $stackPtr  The position of the current token in the stack passed in $tokens.
 * @return void
 */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $found = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, $stackPtr);
        if ($found === false) {
            return;
        }
        if ($tokens[$found - 1]['code'] != T_WHITESPACE) {
            $error = 'Expected 1 space after class declaration, found 0';
            $phpcsFile->addError($error, $found - 1, 'InvalidSpacing');
            return;
        }

        if (strlen($tokens[$found - 1]['content']) > 1 || $tokens[$found - 2]['code'] == T_WHITESPACE) {
            $error = 'Expected 1 space after class declaration, found ' . strlen($tokens[$found - 1]['content']);
            $phpcsFile->addError($error, $found - 1, 'InvalidSpacing');
        }
    }
}
