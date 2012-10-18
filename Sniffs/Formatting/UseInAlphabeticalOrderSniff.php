<?php
/**
 * CakePHP_Sniffs_Formatting_UseInAlphabeticalOrderSniff
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_CakePHP
 * @author    Juan Basso <jrbasso@gmail.com>
 * @copyright Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 */

/**
 * CakePHP_Sniffs_Formatting_UseInAlphabeticalOrderSniff.
 *
 * Ensures all the use are in alphabetical order.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_CakePHP
 * @author    Juan Basso <jrbasso@gmail.com>
 * @copyright Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 */
class CakePHP_Sniffs_Formatting_UseInAlphabeticalOrderSniff implements PHP_CodeSniffer_Sniff {

/**
 * Processed files
 *
 * @var array
 */
	protected $_processed = array();

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
	public function register() {
		return array(T_USE);
	}

/**
 * Processes this test, when one of its tokens is encountered.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
 * @param int $stackPtr The position of the current token in the stack passed in $tokens.
 * @return void
 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		if (isset($this->_processed[$phpcsFile->getFilename()])) {
			return;
		}

		$tokens = $phpcsFile->getTokens();

		$uses = array();
		do {
			$scope = 0;
			if (!empty($tokens[$stackPtr]['conditions'])) {
				$scope = key($tokens[$stackPtr]['conditions']);
			}
			if (!isset($uses[$scope]['__line__'])) {
				$uses[$scope]['__line__'] = $tokens[$stackPtr]['line'];
			}

			$stackPtr += 2; // use keyword and whitespace

			$code = '';
			while (!in_array($tokens[$stackPtr]['code'], array(T_SEMICOLON, T_OPEN_CURLY_BRACKET))) {
				$code .= $tokens[$stackPtr++]['content'];
			}
			foreach (explode(',', $code) as $part) {
				list($use) = explode(' ', $part);
				$use = trim($use, "\n\t\\ ");
			}
			$uses[$scope][] = $use;

			$stackPtr = $phpcsFile->findNext(T_USE, $stackPtr);
		} while ($stackPtr !== false);

		foreach ($uses as $useScope) {
			$line = $useScope['__line__'];
			unset($useScope['__line__']);

			$ordered = $useScope;
			sort($ordered);

			if ($useScope !== $ordered) {
				$error = 'Use classes must be in alphabetical order.';
				$phpcsFile->addError($error, $line, 'UseInAlphabeticalOrder', array());
			}
		}

		$this->_processed[$phpcsFile->getFilename()] = true;
	}

}
