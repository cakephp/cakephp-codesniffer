<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 * @since         CakePHP CodeSniffer 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace CakePHP\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures doc block alignments.
 */
class DocBlockAlignmentSniff implements Sniff
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [T_DOC_COMMENT_OPEN_TAG];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $leftWall = [
            T_CLASS,
            T_NAMESPACE,
            T_INTERFACE,
            T_TRAIT,
            T_USE,
        ];
        $oneIndentation = [
            T_FUNCTION,
            T_VARIABLE,
            T_CONST,
        ];
        $allTokens = array_merge($leftWall, $oneIndentation);
        $notFlatFile = $phpcsFile->findNext(T_NAMESPACE, 0);
        $next = $phpcsFile->findNext($allTokens, $stackPtr + 1);

        if ($next && $notFlatFile) {
            $notWalled = (in_array($tokens[$next]['code'], $leftWall) && $tokens[$stackPtr]['column'] !== 1);
            $notIndented = (in_array($tokens[$next]['code'], $oneIndentation) && $tokens[$stackPtr]['column'] !== 5);
            if ($notWalled || $notIndented) {
                $phpcsFile->addError('Expected docblock to be aligned with code.', $stackPtr, 'NotAllowed');
            }
        }
    }
}
