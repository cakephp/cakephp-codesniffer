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
use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FunctionCommentSniff as PearFunctionCommentSniff;
use PHP_CodeSniffer\Util\Common;

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
class FunctionCommentSniff extends PearFunctionCommentSniff
{
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

    /**
     * Process the function parameter comments.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token in the stack passed in $tokens.
     * @param int $commentStart The position in the stack where the comment started.
     * @return void
     */
    protected function processParams(File $phpcsFile, $stackPtr, $commentStart)
    {
        if ($this->isInheritDoc($phpcsFile, $commentStart)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        $params = [];
        $maxType = $maxVar = 0;
        foreach ($tokens[$commentStart]['comment_tags'] as $pos => $tag) {
            if ($tokens[$tag]['content'] !== '@param') {
                continue;
            }

            $type = $var = $comment = '';
            $typeSpace = $varSpace = 0;
            $commentLines = [];
            if ($tokens[$tag + 2]['code'] === T_DOC_COMMENT_STRING) {
                $matches = [];
                preg_match('/([^$]+)(?:((?:\$|&)[^\s]+)(?:(\s+)(.*))?)?/', $tokens[$tag + 2]['content'], $matches);

                $typeLen = strlen($matches[1]);
                $type = trim($matches[1]);
                $typeSpace = $typeLen - strlen($type);
                $typeLen = strlen($type);
                if ($typeLen > $maxType) {
                    $maxType = $typeLen;
                }

                if (isset($matches[2]) === true) {
                    $var = $matches[2];
                    $varLen = strlen($var);
                    if ($varLen > $maxVar) {
                        $maxVar = $varLen;
                    }

                    if (isset($matches[4]) === true) {
                        $varSpace = strlen($matches[3]);
                        $comment = $matches[4];
                        $commentLines[] = [
                            'comment' => $comment,
                            'token' => $tag + 2,
                            'indent' => $varSpace,
                        ];

                        // Any strings until the next tag belong to this comment.
                        if (isset($tokens[$commentStart]['comment_tags'][$pos + 1]) === true) {
                            $end = $tokens[$commentStart]['comment_tags'][$pos + 1];
                        } else {
                            $end = $tokens[$commentStart]['comment_closer'];
                        }

                        for ($i = $tag + 3; $i < $end; $i++) {
                            if ($tokens[$i]['code'] === T_DOC_COMMENT_STRING) {
                                $indent = 0;
                                if ($tokens[$i - 1]['code'] === T_DOC_COMMENT_WHITESPACE) {
                                    $indent = strlen($tokens[$i - 1]['content']);
                                }

                                $comment .= ' ' . $tokens[$i]['content'];
                                $commentLines[] = [
                                    'comment' => $tokens[$i]['content'],
                                    'token' => $i,
                                    'indent' => $indent,
                                ];
                            }
                        }
                    } else {
                        $error = 'Missing parameter comment';
                        $phpcsFile->addError($error, $tag, 'MissingParamComment');
                        $commentLines[] = ['comment' => ''];
                    }
                } else {
                    $error = 'Missing parameter name';
                    $phpcsFile->addError($error, $tag, 'MissingParamName');
                }
            } else {
                $error = 'Missing parameter type';
                $phpcsFile->addError($error, $tag, 'MissingParamType');
            }

            $params[] = compact('tag', 'type', 'var', 'comment', 'commentLines', 'typeSpace', 'varSpace');
        }

        $realParams = $phpcsFile->getMethodParameters($stackPtr);
        $foundParams = [];

        foreach ($params as $pos => $param) {
            // If the type is empty, the whole line is empty.
            if ($param['type'] === '') {
                continue;
            }

            // Check the param type value.
            $typeNames = explode('|', $param['type']);
            foreach ($typeNames as $typeName) {
                if ($typeName === 'integer') {
                    $suggestedName = 'int';
                } elseif ($typeName === 'boolean') {
                    $suggestedName = 'bool';
                } elseif (in_array($typeName, ['int', 'bool'])) {
                    $suggestedName = $typeName;
                } else {
                    $suggestedName = Common::suggestType($typeName);
                }

                if ($typeName !== $suggestedName) {
                    $error = 'Expected "%s" but found "%s" for parameter type';
                    $data = [$suggestedName, $typeName];

                    $fix = $phpcsFile->addFixableError($error, $param['tag'], 'IncorrectParamVarName', $data);
                    if ($fix === true) {
                        $content = $suggestedName;
                        $content .= str_repeat(' ', $param['typeSpace']);
                        $content .= $param['var'];
                        $content .= str_repeat(' ', $param['varSpace']);
                        if (isset($param['commentLines'][0])) {
                            $content .= $param['commentLines'][0]['comment'];
                        }
                        $phpcsFile->fixer->replaceToken($param['tag'] + 2, $content);
                    }
                }
            }

            if ($param['var'] === '') {
                continue;
            }

            $foundParams[] = $param['var'];

            // Make sure the param name is correct.
            if (isset($realParams[$pos]) === true) {
                $realName = $realParams[$pos]['name'];
                if ($realName !== $param['var']) {
                    $code = 'ParamNameNoMatch';
                    $data = [$param['var'], $realName];

                    $error = 'Doc comment for parameter %s does not match ';
                    if (strtolower($param['var']) === strtolower($realName)) {
                        $error .= 'case of ';
                        $code = 'ParamNameNoCaseMatch';
                    }

                    $error .= 'actual variable name %s';

                    $fix = $phpcsFile->addFixableWarning($error, $param['tag'], $code, $data);

                    if ($fix === true) {
                        $content = $suggestedName;
                        $content .= str_repeat(' ', $param['typeSpace']);
                        $content .= $realName;
                        $content .= str_repeat(' ', $param['varSpace']);
                        $content .= $param['commentLines'][0]['comment'];
                        $phpcsFile->fixer->replaceToken($param['tag'] + 2, $content);
                    }
                }
            } elseif (substr($param['var'], -4) !== ',...') {
                // We must have an extra parameter comment.
                $error = 'Superfluous parameter comment';
                $phpcsFile->addError($error, $param['tag'], 'ExtraParamComment');
            }
        }

        $realNames = [];
        foreach ($realParams as $realParam) {
            $realNames[] = $realParam['name'];
        }

        // Report missing comments.
        $diff = array_diff($realNames, $foundParams);
        foreach ($diff as $neededParam) {
            $error = 'Doc comment for parameter "%s" missing';
            $data = [$neededParam];
            $phpcsFile->addWarning($error, $commentStart, 'MissingParamTag', $data);
        }
    }
}
