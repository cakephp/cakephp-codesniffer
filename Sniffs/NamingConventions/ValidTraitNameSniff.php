<?php
/**
 * CakePHP_Sniffs_NamingConventions_ValidTraitNameSniff
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

if (!defined('T_TRAIT')) {
	define('T_TRAIT', 355);
}

/**
 * CakePHP_Sniffs_NamingConventions_ValidTraitNameSniff.
 *
 * Ensures trait names are correct depending on the folder of the file.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_CakePHP
 * @author    Juan Basso <jrbasso@gmail.com>
 * @copyright Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 */
class CakePHP_Sniffs_NamingConventions_ValidTraitNameSniff implements PHP_CodeSniffer_Sniff {

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
	public function register() {
		return array(T_TRAIT);
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
		$traitName = $tokens[$stackPtr + 2]['content'];

		if (substr($traitName, -5) !== 'Trait') {
			$error = 'Traits must have a "Trait" suffix.';
			$phpcsFile->addError($error, $stackPtr, 'InvalidTraitName', array());
		}
	}

}
