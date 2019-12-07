<?php

namespace CakePHP\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Adds trailing commas in multiline arrays.
 *
 * Heredoc patch taken from slevomat/coding-standard project.
 *
 * @author Mark Scherer
 * @license MIT
 */
class CommaAfterArrayItemSniff implements Sniff
{
    /**
     * @var bool
     */
    public $enableAfterHeredoc = PHP_VERSION_ID >= 70300;

    /**
     * @inheritDoc
     */
    public function register()
    {
        return [
            T_OPEN_SHORT_ARRAY,
        ];
    }

    /**
     * @inheritDoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $arrayToken = $tokens[$stackPtr];
        $closeParenthesisPointer = $arrayToken['bracket_closer'];
        $openParenthesisToken = $tokens[$arrayToken['bracket_opener']];
        $closeParenthesisToken = $tokens[$closeParenthesisPointer];
        if ($openParenthesisToken['line'] === $closeParenthesisToken['line']) {
            return;
        }

        $previousToCloseParenthesisPointer = $phpcsFile->findPrevious(Tokens::$emptyTokens, $closeParenthesisPointer - 1, 0, true);
        $previousToCloseParenthesisToken = $tokens[$previousToCloseParenthesisPointer];
        if (
            $previousToCloseParenthesisPointer === $arrayToken['bracket_opener']
            || $previousToCloseParenthesisToken['code'] === T_COMMA
            || $closeParenthesisToken['line'] === $previousToCloseParenthesisToken['line']
        ) {
            return;
        }
        if (!$this->enableAfterHeredoc && in_array($previousToCloseParenthesisToken['code'], [T_END_HEREDOC, T_END_NOWDOC], true)) {
            return;
        }
        $fix = $phpcsFile->addFixableError(
            'Multi-line arrays must have a trailing comma after the last element.',
            $previousToCloseParenthesisPointer,
            'MissingTrailingComma'
        );
        if (!$fix) {
            return;
        }

        $phpcsFile->fixer->beginChangeset();
        $phpcsFile->fixer->addContent($previousToCloseParenthesisPointer, ',');
        $phpcsFile->fixer->endChangeset();
    }
}
