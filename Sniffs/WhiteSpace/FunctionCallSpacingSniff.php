<?php
/**
 * CakePHP_Sniffs_WhiteSpace_FunctionCallSpacingSniff.
 *
 * Checks the separation between methods in a class or interface.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Mark Story <mark@mark-story.com>
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 */
class CakePHP_Sniffs_WhiteSpace_FunctionCallSpacingSniff implements PHP_CodeSniffer_Sniff {

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
	public function register() {
		return array(
			T_ISSET,
			T_EMPTY,
			T_STRING,
		);
	}

/**
 * Processes this sniff, when one of its tokens is encountered.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
 * @param int $stackPtr The position of the current token in the stack passed in $tokens.
 * @return void
 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();

		// Find the next non-empty token.
		$openBracket = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);

		if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
			// Not a function call.
			return;
		}

		// Look for funcName (
		if (($stackPtr + 1) !== $openBracket) {
			$error = 'Space before opening parenthesis of function call not allowed';
			$phpcsFile->addError($error, $stackPtr, 'SpaceBeforeOpenBracket');
		}
	}

}
