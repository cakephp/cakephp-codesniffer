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
 * @since         CakePHP CodeSniffer 6.0.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Ensures property names follow PSR naming conventions.
 *
 * Non-public properties should not be prefixed with an underscore.
 */
namespace CakePHP\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;

class ValidPropertyNameSniff extends AbstractVariableSniff
{
    /**
     * @inheritDoc
     */
    protected function processMemberVar(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $propName = ltrim($tokens[$stackPtr]['content'], '$');

        // Only check properties starting with underscore
        if ($propName[0] !== '_') {
            return;
        }

        $props = $phpcsFile->getMemberProperties($stackPtr);

        // Public properties with underscore are also bad, but less common
        // Focus on protected/private which was the old convention
        if ($props['scope'] !== 'public') {
            $error = 'Non-public property "$%s" should not be prefixed with underscore';
            $phpcsFile->addError($error, $stackPtr, 'PropertyWithUnderscore', [$propName]);
        } else {
            $error = 'Public property "$%s" must not be prefixed with underscore';
            $phpcsFile->addError($error, $stackPtr, 'PublicPropertyWithUnderscore', [$propName]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function processVariable(File $phpcsFile, $stackPtr)
    {
        // We only care about member variables (properties)
    }

    /**
     * @inheritDoc
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr)
    {
        // We only care about member variables (properties)
    }
}
