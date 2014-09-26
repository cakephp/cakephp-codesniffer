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
 * @since         CakePHP CodeSniffer 0.1.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Makes sure there are spaces between the concatenation operator (.) and
 * the strings being concatenated.
 *
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
 * @param integer $stackPtr The position of the current token in the
 *    stack passed in $tokens.
 * @return void
 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();
		if ($tokens[($stackPtr - 1)]['code'] !== T_WHITESPACE) {
			$message = 'Expected 1 space before ., but 0 found';
			$fix = $phpcsFile->addFixableError($message, $stackPtr, 'MissingBefore');
			$this->_addSpace($fix, $phpcsFile, $stackPtr - 1);
		} else {
			$content = str_replace("\r\n", "\n", $tokens[($stackPtr - 1)]['content']);
			$spaces = strlen($content);
			if ($spaces > 1) {
				$message = 'Expected 1 space before ., but %d found';
				$data = array($spaces);
				$fix = $phpcsFile->addFixableError($message, $stackPtr, 'TooManyBefore', $data);
				$this->_removeSpace($fix, $phpcsFile, $stackPtr - 1);
			}
		}

		if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE) {
			$message = 'Expected 1 space after ., but 0 found';
			$fix = $phpcsFile->addFixableError($message, $stackPtr, 'MissingAfter');
			$this->_addSpace($fix, $phpcsFile, $stackPtr);
		} else {
			$content = str_replace("\r\n", "\n", $tokens[($stackPtr + 1)]['content']);
			$spaces = strlen($content);
			if ($spaces > 1) {
				$message = 'Expected 1 space after ., but %d found';
				$data = array($spaces);
				$fix = $phpcsFile->addFixableError($message, $stackPtr, 'TooManyAfter', $data);
				$this->_removeSpace($fix, $phpcsFile, $stackPtr + 1);
			}
		}
	}

/**
 * Add a single space on the right sight.
 *
 * @param bool $fix Whether or not the fix should be applied.
 * @param object $phpcsFile
 * @param integer $location
 * @return void
 */
	protected function _addSpace($fix, $phpcsFile, $location) {
		if ($fix !== true) {
			return;
		}
		$phpcsFile->fixer->addContent($location, ' ');
	}

/**
 * Remove spaces expect for one on the right sight.
 *
 * @param bool $fix Whether or not the fix should be applied.
 * @param object $phpcsFile
 * @param integer $location
 * @return void
 */
	protected function _removeSpace($fix, $phpcsFile, $location) {
		if ($fix !== true) {
			return;
		}
		//$content = $tokens[$location]['content'];
		$phpcsFile->fixer->replaceToken($location, ' ');
	}

}
