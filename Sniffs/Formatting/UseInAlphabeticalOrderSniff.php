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
 * Declared uses
 *
 * @var array
 */
	protected $uses = array();

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
	public function register() {
		return array(T_USE, T_CLASS);
	}

/**
 * Processes this test, when one of its tokens is encountered.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
 * @param int $stackPtr The position of the current token in the stack passed in $tokens.
 * @return void
 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();
		if ($tokens[$stackPtr]['code'] === T_CLASS) {
			if (!isset($this->uses[$phpcsFile->getFilename()])) {
				return;
			}

			$ordered = $this->uses[$phpcsFile->getFilename()];
			sort($ordered);

			if ($this->uses[$phpcsFile->getFilename()] !== $ordered) {
				$error = 'Use classes must be in alphabetical order.';
				$phpcsFile->addError($error, $stackPtr, 'UseInAlphabeticalOrder', array());
			}
			return;
		}

		$i = 2; // Ignore use word and whitespace
		$filename = $phpcsFile->getFilename();

		$class = '';
		while (in_array($tokens[$stackPtr + $i]['code'], array(T_STRING, T_NS_SEPARATOR))) {
			$class .= $tokens[$stackPtr + $i]['content'];
			$i++;
		}
		$this->uses[$phpcsFile->getFilename()][] = ltrim($class, '\\');
	}

}
