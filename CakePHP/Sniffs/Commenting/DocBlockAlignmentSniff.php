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

/**
 * Ensures doc block alignments.
 */
class CakePHP_Sniffs_Commenting_DocBlockAlignmentSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register() {
        return array(T_DOC_COMMENT_OPEN_TAG);
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param integer              $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $leftWall = array(
            T_CLASS,
            T_NAMESPACE,
            T_INTERFACE,
            T_TRAIT,
            T_USE
        );
        $oneIndentation = array(
            T_FUNCTION,
            T_VARIABLE,
            T_CONST
        );
        $allTokens = array_merge($leftWall, $oneIndentation);

        foreach ($tokens as $key => $token) {
            if ($token['code'] === T_DOC_COMMENT_OPEN_TAG) {
                $next = $phpcsFile->findNext($allTokens, $key + 1, null);
                if ($next) {
                    if (in_array($tokens[$next]['code'], $leftWall) && $token['column'] > 1) {
                        $phpcsFile->addError('Expected docblock to be against left wall.', $key, 'NotAllowed');
                    }
                    if (in_array($tokens[$next]['code'], $oneIndentation) && $token['column'] !== 5) {
                        $phpcsFile->addError('Expected docblock to be indented.', $key, 'NotAllowed');
                    }
                }
            }
        }

        return;
    }
}
