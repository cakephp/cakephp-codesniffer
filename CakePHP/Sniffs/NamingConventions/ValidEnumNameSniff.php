<?php
declare(strict_types=1);

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

        if (!str_ends_with($enumName, 'Enum') && !$this->isInEnumNamespace($phpcsFile)) {
            $error = 'Enums must have an "Enum" suffix (or be in an Enum namespace).';
            $phpcsFile->addError($error, $stackPtr, 'InvalidEnumName');
        }
    }

    /**
     * Check if the file's namespace contains "Enum" as a segment.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @return bool
     */
    protected function isInEnumNamespace(File $phpcsFile): bool
    {
        $tokens = $phpcsFile->getTokens();
        $namespacePtr = $phpcsFile->findNext(T_NAMESPACE, 0);

        if ($namespacePtr === false) {
            return false;
        }

        $namespaceEnd = $phpcsFile->findNext([T_SEMICOLON, T_OPEN_CURLY_BRACKET], $namespacePtr);
        $namespace = '';

        for ($i = $namespacePtr + 1; $i < $namespaceEnd; $i++) {
            if ($tokens[$i]['code'] === T_STRING || $tokens[$i]['code'] === T_NAME_QUALIFIED) {
                $namespace .= $tokens[$i]['content'];
            }
        }

        // Check if namespace ends with \Enum or contains \Enum\
        return (bool)preg_match('/\\\\Enum(\\\\|$)/', $namespace);
    }
}
