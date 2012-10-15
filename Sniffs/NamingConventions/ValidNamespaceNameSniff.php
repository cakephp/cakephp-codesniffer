<?php
/**
 * CakePHP_Sniffs_NamingConventions_ValidFunctionNameSniff
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
 * CakePHP_Sniffs_NamingConventions_ValidNamespaceNameSniff.
 *
 * Ensures namespace names are correct depending on the folder of the file.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_CakePHP
 * @author    Juan Basso <jrbasso@gmail.com>
 * @copyright Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 */
class CakePHP_Sniffs_NamingConventions_ValidNamespaceNameSniff implements PHP_CodeSniffer_Sniff {

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
	public function register() {
		return array(T_NAMESPACE);
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
		$i = 2; // Ignore namespace word and whitespace
		$filename = $phpcsFile->getFilename();
		$ns = '';

		while ($tokens[$stackPtr + $i]['code'] !== T_SEMICOLON) {
			$ns .= $tokens[$stackPtr + $i]['content'];
			$i++;
		}

		$ns = '\\' . ltrim($ns, '\\');
		$path = dirname($filename);

		if (substr(str_replace('/', '\\', $path), -1 * strlen($ns)) !== $ns) {
			$error = 'Namespace does not match with the directory name';
			$phpcsFile->addError($error, $stackPtr, 'InvalidNamespace', array());
		}
	}

}
