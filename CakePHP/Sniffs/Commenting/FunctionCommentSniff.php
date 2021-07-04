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
 * @since         CakePHP CodeSniffer 0.1.24
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakePHP\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Parses and verifies the doc comments for functions.
 *
 * Verifies that :
 * <ul>
 *  <li>A comment exists</li>
 *  <li>There is a blank newline after the short description</li>
 *  <li>There is a blank newline between the long and short description</li>
 *  <li>There is a blank newline between the long description and tags</li>
 *  <li>Parameter names represent those in the method</li>
 *  <li>Parameter comments are in the correct order</li>
 *  <li>Parameter comments are complete</li>
 *  <li>A type hint is provided for array and custom class</li>
 *  <li>Type hint matches the actual variable/class type</li>
 *  <li>A blank line is present before the first and after the last parameter</li>
 *  <li>A return type exists</li>
 *  <li>Any throw tag must have a comment</li>
 *  <li>The tag order and indentation are correct</li>
 * </ul>
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class FunctionCommentSniff implements Sniff
{
    /**
     * Disable the check for functions with a lower visibility than the value given.
     *
     * Allowed values are public, protected, and private.
     *
     * @var string
     */
    public $minimumVisibility = 'private';

    /**
     * Array of methods which do not require a return type.
     *
     * @var array
     */
    public $specialMethods = [
        '__construct',
        '__destruct',
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token in the stack passed in $tokens.
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $scopeModifier = $phpcsFile->getMethodProperties($stackPtr)['scope'];
        if (
            $scopeModifier === 'protected'
            && $this->minimumVisibility === 'public'
            || $scopeModifier === 'private'
            && ($this->minimumVisibility === 'public' || $this->minimumVisibility === 'protected')
        ) {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $ignore = Tokens::$methodPrefixes;
        $ignore[] = T_WHITESPACE;

        $commentEnd = $phpcsFile->findPrevious($ignore, $stackPtr - 1, null, true);
        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            // Inline comments might just be closing comments for
            // control structures or functions instead of function comments
            // using the wrong comment type. If there is other code on the line,
            // assume they relate to that code.
            $prev = $phpcsFile->findPrevious($ignore, $commentEnd - 1, null, true);
            if ($prev !== false && $tokens[$prev]['line'] === $tokens[$commentEnd]['line']) {
                $commentEnd = $prev;
            }
        }

        if (
            $tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT
        ) {
            $previous = $commentEnd;
            if (
                $tokens[$commentEnd]['type'] === 'T_ATTRIBUTE_END'
                || $tokens[$commentEnd]['type'] === 'T_ATTRIBUTE'
            ) {
                while ($tokens[$previous]['type'] !== 'T_ATTRIBUTE') {
                    $previous--;
                }
                $previous--;

                $commentEnd = $phpcsFile->findPrevious($ignore, $previous, null, true);
                if ($tokens[$commentEnd]['code'] === T_DOC_COMMENT_CLOSE_TAG) {
                    if ($tokens[$commentEnd]['line'] !== $tokens[$previous]['line'] - 1) {
                        $error = 'There must be no blank lines after the function comment';
                        $phpcsFile->addError($error, $commentEnd, 'SpacingAfter');
                    }

                    return;
                }
            }

            $function = $phpcsFile->getDeclarationName($stackPtr);
            $phpcsFile->addError(
                'Missing doc comment for function %s()',
                $stackPtr,
                'Missing',
                [$function]
            );
            $phpcsFile->recordMetric($stackPtr, 'Function has doc comment', 'no');

            return;
        } else {
            $phpcsFile->recordMetric($stackPtr, 'Function has doc comment', 'yes');
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError('You must use "/**" style comments for a function comment', $stackPtr, 'WrongStyle');

            return;
        }

        if ($tokens[$commentEnd]['line'] !== $tokens[$stackPtr]['line'] - 1) {
            $error = 'There must be no blank lines after the function comment';
            $phpcsFile->addError($error, $commentEnd, 'SpacingAfter');
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === '@see') {
                // Make sure the tag isn't empty.
                $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                if ($string === false || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                    $error = 'Content missing for @see tag in function comment';
                    $phpcsFile->addError($error, $tag, 'EmptySees');
                }
            }
        }

        $this->processReturn($phpcsFile, $stackPtr, $commentStart);
        $this->processThrows($phpcsFile, $stackPtr, $commentStart);
    }

    /**
     * Checks if the doc comment is an inheritDoc comment.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $commentStart The position in the stack where the comment started.
     * @return bool True if the comment is an inheritdoc
     */
    protected function isInheritDoc(File $phpcsFile, $commentStart)
    {
        $tokens = $phpcsFile->getTokens();

        $empty = [
            T_DOC_COMMENT_WHITESPACE,
            T_DOC_COMMENT_STAR,
        ];

        $commentEnd = $tokens[$commentStart]['comment_closer'];
        $inheritDoc = $phpcsFile->findNext($empty, $commentStart + 1, $commentEnd, true);
        if ($inheritDoc === false) {
            return false;
        }

        if (preg_match('/^@inheritDoc$/i', $tokens[$inheritDoc]['content']) === 1) {
            return true;
        }

        if (preg_match('/^{@inheritDoc}$/i', $tokens[$inheritDoc]['content']) !== 1) {
            return false;
        }

        $notAllowed = ['@param', '@return'];
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if (in_array($tokens[$tag]['content'], $notAllowed, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Process the return comment of this function comment.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token in the stack passed in $tokens.
     * @param int $commentStart The position in the stack where the comment started.
     * @return void
     */
    protected function processReturn(File $phpcsFile, $stackPtr, $commentStart)
    {
        if ($this->isInheritDoc($phpcsFile, $commentStart)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        // Skip constructor and destructor.
        $className = '';
        foreach ($tokens[$stackPtr]['conditions'] as $condPtr => $condition) {
            if ($condition === T_CLASS || $condition === T_INTERFACE) {
                $className = $phpcsFile->getDeclarationName($condPtr);
                $className = strtolower(ltrim($className, '_'));
            }
        }

        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        $isSpecialMethod = ($methodName === '__construct' || $methodName === '__destruct');
        if ($methodName !== '_') {
            $methodName = strtolower(ltrim($methodName, '_'));
        }

        $return = null;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === '@return') {
                if ($return !== null) {
                    $error = 'Only 1 @return tag is allowed in a function comment';
                    $phpcsFile->addError($error, $tag, 'DuplicateReturn');

                    return;
                }

                $return = $tag;
            }
        }

        if ($isSpecialMethod === true) {
            return;
        }

        if ($return === null) {
            $error = 'Missing @return tag in function comment';
            $phpcsFile->addWarning($error, $tokens[$commentStart]['comment_closer'], 'MissingReturn');

            return;
        }

        $content = $tokens[$return + 2]['content'];
        if (empty($content) === true || $tokens[$return + 2]['code'] !== T_DOC_COMMENT_STRING) {
            $error = 'Return type missing for @return tag in function comment';
            $phpcsFile->addError($error, $return, 'MissingReturnType');

            return;
        }

        $endToken = $tokens[$stackPtr]['scope_closer'] ?? false;
        if (!$endToken) {
            return;
        }

        [$types, ] = explode(' ', $content);
        $typeNames = explode('|', $types);

        // If the return type is void, make sure there is
        // no non-void return statements in the function.
        if ($typeNames === ['void']) {
            for ($returnToken = $stackPtr; $returnToken < $endToken; $returnToken++) {
                if ($tokens[$returnToken]['code'] === T_CLOSURE) {
                    $returnToken = $tokens[$returnToken]['scope_closer'];
                    continue;
                }

                if (
                    $tokens[$returnToken]['code'] === T_RETURN
                    || $tokens[$returnToken]['code'] === T_YIELD
                    || $tokens[$returnToken]['code'] === T_YIELD_FROM
                ) {
                    break;
                }
            }

            if ($returnToken !== $endToken) {
                // If the function is not returning anything, just
                // exiting, then there is no problem.
                $semicolon = $phpcsFile->findNext(T_WHITESPACE, $returnToken + 1, null, true);
                if ($tokens[$semicolon]['code'] !== T_SEMICOLON) {
                    $error = 'Function return type is void, but function contains return statement';
                    $phpcsFile->addWarning($error, $return, 'InvalidReturnVoid');
                }
            }

            return;
        }

        // If return type is not void, there needs to be a return statement
        // somewhere in the function that returns something.
        if (!in_array('mixed', $typeNames, true) && !in_array('void', $typeNames, true)) {
            $returnToken = $phpcsFile->findNext([T_RETURN, T_YIELD, T_YIELD_FROM], $stackPtr, $endToken);
            if ($returnToken === false && !$this->hasException($phpcsFile, $stackPtr, $endToken)) {
                $error = 'Function return type is not void, but function has no return statement';
                $phpcsFile->addWarning($error, $return, 'InvalidNoReturn');
            } else {
                $semicolon = $phpcsFile->findNext(T_WHITESPACE, $returnToken + 1, null, true);
                if ($tokens[$semicolon]['code'] === T_SEMICOLON) {
                    $error = 'Function return type is not void, but function is returning void here';
                    $phpcsFile->addWarning($error, $returnToken, 'InvalidReturnNotVoid');
                }
            }
        }
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile File
     * @param int $startIndex Start index
     * @param int $endIndex End index
     * @return bool
     */
    protected function hasException(File $phpcsFile, $startIndex, $endIndex)
    {
        $throwIndex = $phpcsFile->findNext([T_THROW], $startIndex, $endIndex);

        return $throwIndex !== false;
    }

    /**
     * Process any throw tags that this function comment has.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token in the stack passed in $tokens.
     * @param int $commentStart The position in the stack where the comment started.
     * @return void
     */
    protected function processThrows(File $phpcsFile, $stackPtr, $commentStart)
    {
        $tokens = $phpcsFile->getTokens();

        foreach ($tokens[$commentStart]['comment_tags'] as $pos => $tag) {
            if ($tokens[$tag]['content'] !== '@throws') {
                continue;
            }

            $exception = $comment = null;
            if ($tokens[$tag + 2]['code'] === T_DOC_COMMENT_STRING) {
                $matches = [];
                preg_match('/([^\s]+)(?:\s+(.*))?/', $tokens[$tag + 2]['content'], $matches);
                $exception = $matches[1];
                if (isset($matches[2]) === true) {
                    $comment = $matches[2];
                }
            }

            if ($exception === null) {
                $error = 'Exception type and comment missing for @throws tag in function comment';
                $phpcsFile->addWarning($error, $tag, 'InvalidThrows');
            }
        }
    }
}
