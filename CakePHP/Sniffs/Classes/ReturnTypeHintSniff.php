<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace CakePHP\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use SlevomatCodingStandard\Helpers\ClassHelper;
use SlevomatCodingStandard\Helpers\EmptyFileException;
use SlevomatCodingStandard\Helpers\NamespaceHelper;
use SlevomatCodingStandard\Helpers\TokenHelper;

/**
 * In method return types self for chaining methods is disallowed as it is poorly supported by the language.
 *
 * @author Mark Scherer
 */
class ReturnTypeHintSniff implements Sniff
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * @inheritDoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $openParenthesisIndex = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr + 1);
        $closeParenthesisIndex = $tokens[$openParenthesisIndex]['parenthesis_closer'];

        $colonIndex = $phpcsFile->findNext(Tokens::$emptyTokens, $closeParenthesisIndex + 1, null, true);

        if (!$this->isChainingMethod($phpcsFile, $stackPtr)) {
            if ($colonIndex && $tokens[$colonIndex]['code'] === T_COLON) {
                $this->assertNotThisOrStatic($phpcsFile, $stackPtr);
            }

            return;
        }

        // We skip for interface methods
        if (empty($tokens[$stackPtr]['scope_opener']) || empty($tokens[$stackPtr]['scope_closer'])) {
            return;
        }

        // No colon means no return type hint - add static
        if (!$colonIndex || $tokens[$colonIndex]['code'] !== T_COLON) {
            $fix = $phpcsFile->addFixableError(
                'Chaining methods (@return $this) should have "static" return type.',
                $closeParenthesisIndex,
                'MissingStatic',
            );
            if (!$fix) {
                return;
            }

            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->addContent($closeParenthesisIndex, ': static');
            $phpcsFile->fixer->endChangeset();

            return;
        }

        $startIndex = $phpcsFile->findNext(Tokens::$emptyTokens, $colonIndex + 1, $colonIndex + 3, true);
        if (!$startIndex) {
            return;
        }

        $returnTokenCode = $tokens[$startIndex]['code'];
        if ($returnTokenCode === T_STATIC) {
            return;
        }

        if ($returnTokenCode !== T_SELF) {
            $fix = $phpcsFile->addFixableError(
                'Chaining methods (@return $this) should have "static" return type.',
                $startIndex,
                'InvalidSelf',
            );
            if (!$fix) {
                return;
            }

            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($startIndex, 'static');
            $phpcsFile->fixer->endChangeset();

            return;
        }

        $fix = $phpcsFile->addFixableError(
            'Chaining methods (@return $this) should have "static" return type instead of "self".',
            $startIndex,
            'InvalidSelf',
        );
        if (!$fix) {
            return;
        }

        $phpcsFile->fixer->beginChangeset();
        $phpcsFile->fixer->replaceToken($startIndex, 'static');
        $phpcsFile->fixer->endChangeset();
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpCsFile File
     * @param int $stackPointer Stack pointer
     * @return bool
     */
    protected function isChainingMethod(File $phpCsFile, int $stackPointer): bool
    {
        $docBlockEndIndex = $this->findRelatedDocBlock($phpCsFile, $stackPointer);

        if (!$docBlockEndIndex) {
            return false;
        }

        $tokens = $phpCsFile->getTokens();

        $docBlockStartIndex = $tokens[$docBlockEndIndex]['comment_opener'];

        for ($i = $docBlockStartIndex + 1; $i < $docBlockEndIndex; $i++) {
            if ($tokens[$i]['code'] !== T_DOC_COMMENT_TAG) {
                continue;
            }
            if ($tokens[$i]['content'] !== '@return') {
                continue;
            }

            $classNameIndex = $i + 2;

            if ($tokens[$classNameIndex]['code'] !== T_DOC_COMMENT_STRING) {
                continue;
            }

            $content = $tokens[$classNameIndex]['content'];
            if (!$content) {
                continue;
            }

            return $content === '$this';
        }

        return false;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpCsFile File
     * @param int $stackPointer Stack pointer
     * @return void
     */
    protected function assertNotThisOrStatic(File $phpCsFile, int $stackPointer): void
    {
        $docBlockEndIndex = $this->findRelatedDocBlock($phpCsFile, $stackPointer);

        if (!$docBlockEndIndex) {
            return;
        }

        $tokens = $phpCsFile->getTokens();

        $docBlockStartIndex = $tokens[$docBlockEndIndex]['comment_opener'];

        for ($i = $docBlockStartIndex + 1; $i < $docBlockEndIndex; $i++) {
            if ($tokens[$i]['code'] !== T_DOC_COMMENT_TAG) {
                continue;
            }
            if ($tokens[$i]['content'] !== '@return') {
                continue;
            }

            $classNameIndex = $i + 2;

            if ($tokens[$classNameIndex]['code'] !== T_DOC_COMMENT_STRING) {
                continue;
            }

            $content = $tokens[$classNameIndex]['content'];
            if (!$content || strpos($content, '\\') !== 0) {
                continue;
            }

            $classNameWithNamespace = $this->getClassNameWithNamespace($phpCsFile);
            if ($content !== $classNameWithNamespace) {
                continue;
            }

            $phpCsFile->addError(
                'Class name repeated, expected `static` or `$this`.',
                $classNameIndex,
                'InvalidClass',
            );
        }
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpCsFile File
     * @param int $stackPointer Stack pointer
     * @return int|null Stackpointer value of docblock end tag, or null if cannot be found
     */
    protected function findRelatedDocBlock(File $phpCsFile, int $stackPointer): ?int
    {
        $tokens = $phpCsFile->getTokens();

        $line = $tokens[$stackPointer]['line'];
        $beginningOfLine = $stackPointer;
        while (!empty($tokens[$beginningOfLine - 1]) && $tokens[$beginningOfLine - 1]['line'] === $line) {
            $beginningOfLine--;
        }

        if (
            !empty($tokens[$beginningOfLine - 2])
            && $tokens[$beginningOfLine - 2]['code'] === T_DOC_COMMENT_CLOSE_TAG
        ) {
            return $beginningOfLine - 2;
        }

        if (
            !empty($tokens[$beginningOfLine - 3])
            && $tokens[$beginningOfLine - 3]['code'] === T_DOC_COMMENT_CLOSE_TAG
        ) {
            return $beginningOfLine - 3;
        }

        return null;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpCsFile File
     * @return string|null
     */
    protected function getClassNameWithNamespace(File $phpCsFile): ?string
    {
        try {
            $lastToken = TokenHelper::getLastTokenPointer($phpCsFile);
        } catch (EmptyFileException $e) {
            return null;
        }

        if (!NamespaceHelper::findCurrentNamespaceName($phpCsFile, $lastToken)) {
            return null;
        }

        $classPointer = $phpCsFile->findPrevious(
            [T_CLASS, T_TRAIT, T_INTERFACE, T_ENUM],
            $lastToken,
        );
        if (!$classPointer) {
            return null;
        }

        return ClassHelper::getFullyQualifiedName(
            $phpCsFile,
            $classPointer,
        );
    }
}
