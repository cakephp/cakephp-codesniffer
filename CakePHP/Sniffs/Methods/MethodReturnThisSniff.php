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
 * @since         CakePHP CodeSniffer 5.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace CakePHP\Sniffs\Methods;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Util\Tokens;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

class MethodReturnThisSniff extends AbstractScopeSniff
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(Tokens::$ooScopeTokens, [T_FUNCTION]);
    }

    /**
     * Processes the function tokens within the class.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int $stackPtr  The position where the token was found.
     * @param int $currScope The current scope opener token.
     * @return void
     */
    protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope)
    {
        $tokens = $phpcsFile->getTokens();

        $docCommentEnd = $phpcsFile->findPrevious(
            [T_DOC_COMMENT_CLOSE_TAG, T_SEMICOLON, T_CLOSE_CURLY_BRACKET, T_OPEN_CURLY_BRACKET],
            $stackPtr - 1,
            null
        );
        if ($docCommentEnd === false || $tokens[$docCommentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG) {
            // Ignore methods without docblocks
            return;
        }

        $docCommentOpen = $tokens[$docCommentEnd]['comment_opener'];
        foreach ($tokens[$docCommentOpen]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] !== '@return') {
                continue;
            }

            $tagComment = $phpcsFile->fixer->getTokenContent($tag + 2);
            $valueNode = self::getValueNode($tokens[$tag]['content'], $tagComment);
            if (!$valueNode instanceof ReturnTagValueNode || !$valueNode->type instanceof ThisTypeNode) {
                continue;
            }

            $method = $phpcsFile->getMethodProperties($stackPtr);
            if ($method['return_type'] !== 'static') {
                $returnStartToken = $method['return_type_token'];
                if ($returnStartToken !== false) {
                    $fix = $phpcsFile->addFixableError(
                        'Method with @return $this annotation must have static return type hint.',
                        $returnStartToken,
                        'MissingStaticForThis'
                    );

                    if (!$fix) {
                        continue;
                    }

                    $phpcsFile->fixer->beginChangeset();

                    if ($method['nullable_return_type']) {
                        $phpcsFile->fixer->replaceToken($method['return_type_token'] - 1, '');
                    }
                    $phpcsFile->fixer->replaceToken($method['return_type_token'], 'static');
                    while (++$returnStartToken <= $method['return_type_end_token']) {
                        $phpcsFile->fixer->replaceToken($returnStartToken, '');
                    }

                    $phpcsFile->fixer->endChangeset();

                    continue;
                }

                $scopeOpener = $tokens[$stackPtr]['scope_opener'];
                if (!$scopeOpener) {
                    continue;
                }
                $endParenthesis = $phpcsFile->findPrevious([T_CLOSE_PARENTHESIS, T_COLON], $scopeOpener - 1, null);
                if (!$endParenthesis || $tokens[$endParenthesis]['code'] !== T_CLOSE_PARENTHESIS) {
                    continue;
                }

                $fix = $phpcsFile->addFixableError(
                    'Method with @return $this annotation must have static return type hint.',
                    $endParenthesis,
                    'MissingStaticForThis'
                );

                if (!$fix) {
                    continue;
                }

                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->addContent($endParenthesis, ': static');
                $phpcsFile->fixer->endChangeset();
            }
        }
    }

    /**
     * Processes a token that is found outside the scope that this test is
     * listening to.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int  $stackPtr The position in the stack where this
     *                       token was found.
     * @return int|void Optionally returns a stack pointer. The sniff will not be
     *                  called again on the current file until the returned stack
     *                  pointer is reached. Return (count($tokens) + 1) to skip
     *                  the rest of the file.
     */
    protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
    {
    }

    /**
     * @param string $tagName tag name
     * @param string $tagComment tag comment
     * @return \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
     */
    protected static function getValueNode(string $tagName, $tagComment): PhpDocTagValueNode
    {
        static $phpDocParser;
        if (!$phpDocParser) {
            $constExprParser = new ConstExprParser();
            $phpDocParser = new PhpDocParser(new TypeParser($constExprParser), $constExprParser);
        }

        static $phpDocLexer;
        if (!$phpDocLexer) {
            $phpDocLexer = new Lexer();
        }

        return $phpDocParser->parseTagValue(new TokenIterator($phpDocLexer->tokenize($tagComment)), $tagName);
    }
}
