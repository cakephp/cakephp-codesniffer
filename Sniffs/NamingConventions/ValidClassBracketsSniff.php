<?php
/**
 * CakePHP_Sniffs_NamingConventions_ValidClassBracketsSniff
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_CakePHP
 * @author    Rik van der Heijden <rik.vander.heijden@gmail.com>
 * @copyright Copyright 2005-2013, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 */

/**
 * CakePHP_Sniffs_NamingConventions_ValidClassBracketsSniff.
 *
 * Ensures curly brackets are on the same line as the Class declaration
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_CakePHP
 * @author    Rik van der Heijden <rik.vander.heijden@gmail.com>
 * @copyright Copyright 2005-2013, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 */
class CakePHP_Sniffs_NamingConventions_ValidClassBracketsSniff implements PHP_CodeSniffer_Sniff {

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
	public function register() {
		return array(T_CLASS);
	}

/**
 * Processes this test, when one of its tokens is encountered.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
 * @param int $stackPtr  The position of the current token in the stack passed in $tokens.
 * @return void
 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();

		$found = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, $stackPtr);
		if ($tokens[$found - 1]['code'] != T_WHITESPACE) {
			$error = 'Expected 1 space after class declaration, found 0';
			$phpcsFile->addError($error, $found - 1, 'InvalidSpacing', array());
			return;
		} else if ($tokens[$found - 1]['content'] != " ") {
			$error = 'Expected 1 space before curly opening bracket';
			$phpcsFile->addError($error, $found - 1, 'InvalidBracketPlacement', array());
		}

		if (strlen($tokens[$found - 1]['content']) > 1 || $tokens[$found - 2]['code'] == T_WHITESPACE) {
			$error = 'Expected 1 space after class declaration, found ' . strlen($tokens[$found - 1]['content']);
			$phpcsFile->addError($error, $found - 1, 'InvalidSpacing', array());
		}
	}

}

