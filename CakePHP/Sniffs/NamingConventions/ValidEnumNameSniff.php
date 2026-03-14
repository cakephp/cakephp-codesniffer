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
 * @since         CakePHP CodeSniffer 0.1.10
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Ensures enum names use the Enum suffix.
 */
namespace CakePHP\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ValidEnumNameSniff implements Sniff
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        return [T_ENUM];
    }

    /**
     * @inheritDoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $enumName = $phpcsFile->getDeclarationName($stackPtr);

        if (!str_ends_with($enumName, 'Enum')) {
            $error = 'Enums must have an "Enum" suffix.';
            $phpcsFile->addError($error, $stackPtr, 'InvalidEnumName');
        }
    }
}
