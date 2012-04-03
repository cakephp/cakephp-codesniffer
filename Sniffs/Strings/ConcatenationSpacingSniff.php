<?php
/**
 * CakePHP_Sniffs_Strings_ConcatenationSpacingSniff.
 *
 * Makes sure there are no spaces between the concatenation operator (.) and
 * the strings being concatenated.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.3.0
 */
class CakePHP_Sniffs_Strings_ConcatenationSpacingSniff implements PHP_CodeSniffer_Sniff {

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
	public function register() {
		return array(T_STRING_CONCAT);
	}

/**
 * Processes this test, when one of its tokens is encountered.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
 * @param int $stackPtr The position of the current token in the
 *    stack passed in $tokens.
 * @return void
 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();
		if ($tokens[($stackPtr - 1)]['code'] !== T_WHITESPACE) {
			$message = 'Expected 1 space before ., but 0 found';
			$phpcsFile->addError($message, $stackPtr, 'MissingBefore');
		} else {
			$spaces = strlen($tokens[($stackPtr - 1)]['content']);
			if ($spaces > 1) {
				$message = 'Expected 1 space before ., but %d found';
				$data = array($spaces);
				$phpcsFile->addError($message, $stackPtr, 'TooManyBefore', $data);
			}
		}

		if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE) {
			$message = 'Expected 1 space after ., but 0 found';
			$phpcsFile->addError($message, $stackPtr, 'MissingAfter');
		} else {
			$spaces = strlen($tokens[($stackPtr + 1)]['content']);
			if ($spaces > 1) {
				$message = 'Expected 1 space after ., but %d found';
				$data = array($spaces);
				$phpcsFile->addError($message, $stackPtr, 'TooManyAfter', $data);
			}
		}
	}

}
