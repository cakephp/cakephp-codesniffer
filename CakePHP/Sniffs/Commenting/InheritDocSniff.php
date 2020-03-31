<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://github.com/cakephp/cakephp-codesniffer
 * @since         CakePHP CodeSniffer 4.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace CakePHP\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Validates proper @inheritDoc use.
 */
class InheritDocSniff implements Sniff
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        return [T_DOC_COMMENT_OPEN_TAG];
    }

    /**
     * @inheritDoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $commentStart = $stackPtr;
        $commentEnd = $tokens[$stackPtr]['comment_closer'];

        $empty = [
            T_DOC_COMMENT_WHITESPACE,
            T_DOC_COMMENT_STAR,
        ];

        $comment = $phpcsFile->findNext($empty, $stackPtr + 1, $commentEnd, true);
        if ($comment === false) {
            // Ignore empty comments
            return;
        }

        if (
            preg_match('/^@inheritDoc$/i', $tokens[$comment]['content']) === 1 &&
            $phpcsFile->findNext($empty, $comment + 1, $commentEnd, true) !== false
        ) {
            $msg = '@inheritDoc doc comments must not contain anything else';
            $phpcsFile->addError($msg, $comment, 'InvalidInheritDoc');
        }
    }
}
