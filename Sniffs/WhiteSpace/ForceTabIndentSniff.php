<?php
/**
 * CakePHP_Sniffs_WhiteSpace_ForceTabIndentSniff
 *
 * Check for any line starting with 2 spaces - which would indicate space indenting
 * Also check for "\t " - a tab followed by a space, which is a common similar mistake
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_CakePHP
 * @author    Andy Dawson <andydawson76@gmail.com>
 * @copyright Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   Release: 1.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 */
class CakePHP_Sniffs_WhiteSpace_ForceTabIndentSniff extends Generic_Sniffs_WhiteSpace_DisallowTabIndentSniff
{

/**
 * Processes this test, when one of its tokens is encountered.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
 * @param int $stackPtr  The position of the current token
 *    in the stack passed in $tokens.
 * @return void
 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();

		$line = $tokens[$stackPtr]['line'];
		if ($stackPtr > 0 && $tokens[($stackPtr - 1)]['line'] === $line) {
			return;
		}

		if (preg_match('@^  @', $tokens[$stackPtr]['content'])) {
			$error = 'Space indented: Tabs for indents, spaces for alignment';
			$phpcsFile->addError($error, $stackPtr);
		} elseif (preg_match('@\t [^\*]@', $tokens[$stackPtr]['content'])) {
			$error = 'Tab followed by space - bad indentation';
			$phpcsFile->addWarning($error, $stackPtr);
		}
	}

}
