<?php
namespace CakePHP\Sniffs\Formatting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Throws errors if there's no blank line before return statements.
 *
 * @author   Authors <Symfony2-coding-standard@escapestudios.github.com>
 * @license  http://spdx.org/licenses/MIT MIT License
 * @link     https://github.com/escapestudios/Symfony2-coding-standard
 */
class BlankLineBeforeReturnSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [
        'PHP',
        'JS',
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_RETURN];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile All the tokens found in the document.
     * @param int $stackPtr The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $current = $stackPtr;
        $previousLine = $tokens[$stackPtr]['line'] - 1;
        $prevLineTokens = [];

        while ($current >= 0 && $tokens[$current]['line'] >= $previousLine) {
            if ($tokens[$current]['line'] == $previousLine
                && $tokens[$current]['type'] !== 'T_WHITESPACE'
                && $tokens[$current]['type'] !== 'T_COMMENT'
                && $tokens[$current]['type'] !== 'T_DOC_COMMENT_CLOSE_TAG'
                && $tokens[$current]['type'] !== 'T_DOC_COMMENT_WHITESPACE'
            ) {
                $prevLineTokens[] = $tokens[$current]['type'];
            }
            $current--;
        }

        if (isset($prevLineTokens[0])
            && ($prevLineTokens[0] === 'T_OPEN_CURLY_BRACKET'
                || $prevLineTokens[0] === 'T_COLON'
                || $prevLineTokens[0] === 'T_OPEN_TAG')
        ) {
            return;
        } elseif (count($prevLineTokens) > 0) {
            $fix = $phpcsFile->addError(
                'Missing blank line before return statement',
                $stackPtr,
                'BlankLineBeforeReturn'
            );
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->addContentBefore($stackPtr, "\n\n");
                $phpcsFile->fixer->endChangeset();
            }
        }

        return;
    }
}
