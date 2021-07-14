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
namespace CakePHP\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

/**
 * Verifies order of types in type hints
 */
class TypeHintSniff implements Sniff
{
    /**
     * @var bool
     */
    public $convertArraysToGenerics = true;

    protected static $typeHintTags = [
        '@var',
        '@psalm-var',
        '@phpstan-var',
        '@param',
        '@psalm-param',
        '@phpstan-param',
        '@return',
        '@psalm-return',
        '@phpstan-return',
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_DOC_COMMENT_OPEN_TAG];
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
        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$stackPtr]['comment_closer'])) {
            return;
        }

        foreach ($tokens[$stackPtr]['comment_tags'] as $tag) {
            if (
                $tokens[$tag + 2]['code'] !== T_DOC_COMMENT_STRING ||
                !in_array($tokens[$tag]['content'], self::$typeHintTags, true)
            ) {
                continue;
            }

            $tagComment = $phpcsFile->fixer->getTokenContent($tag + 2);
            $valueNode = self::getValueNode($tokens[$tag]['content'], $tagComment);
            if ($valueNode instanceof InvalidTagValueNode) {
                continue;
            }

            if ($valueNode->type instanceof UnionTypeNode) {
                $types = $valueNode->type->types;
            } elseif ($valueNode->type instanceof ArrayTypeNode) {
                $types = [$valueNode->type];
            } else {
                continue;
            }

            $originalTypeHint = $this->renderUnionTypes($types);
            $sortedTypeHint = $this->getSortedTypeHint($types);
            if ($sortedTypeHint === $originalTypeHint) {
                continue;
            }

            $fix = $phpcsFile->addFixableWarning(
                '%s type hint is not formatted properly, expected "%s"',
                $tag,
                'IncorrectFormat',
                [$tokens[$tag]['content'], $sortedTypeHint]
            );
            if (!$fix) {
                continue;
            }

            $newComment = $tagComment;
            if ($valueNode instanceof VarTagValueNode) {
                $newComment = trim(sprintf(
                    '%s %s %s',
                    $sortedTypeHint,
                    $valueNode->variableName,
                    $valueNode->description
                ));
                if ($tagComment[-1] === ' ') {
                    // tags above variables in code have a trailing space
                    $newComment .= ' ';
                }
            } elseif ($valueNode instanceof ParamTagValueNode) {
                $newComment = trim(sprintf(
                    '%s %s%s %s',
                    $sortedTypeHint,
                    $valueNode->isVariadic ? '...' : '',
                    $valueNode->parameterName,
                    $valueNode->description
                ));
            } elseif ($valueNode instanceof ReturnTagValueNode) {
                $newComment = trim(sprintf(
                    '%s %s',
                    $sortedTypeHint,
                    $valueNode->description
                ));
            }

            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($tag + 2, $newComment);
            $phpcsFile->fixer->endChangeset();
        }
    }

    /**
     * @param array $types node types
     * @return string
     */
    protected function getSortedTypeHint(array $types): string
    {
        static $shouldSort = [
            '\\Closure',
            '\\Traversable',
            '\\ArrayAccess',
            '\\Stringable',
            '\\Generator',
            'mixed',
            'callable',
            'resource',
            'object',
            'iterable',
            'array',
            'callable-string',
            'class-string',
            'interface-string',
            'string',
            'float',
            'int',
            'bool',
            'true',
            'false',
            'null',
            'void',
        ];

        $sortable = array_fill_keys($shouldSort, []);
        $unsortable = [];
        foreach ($types as $type) {
            $sortName = null;
            if ($type instanceof IdentifierTypeNode) {
                $sortName = $type->name;
            } elseif ($type instanceof NullableTypeNode) {
                if ($type->type instanceof IdentifierTypeNode) {
                    $sortName = $type->type->name;
                }
            } elseif ($type instanceof ArrayTypeNode) {
                if ($this->convertArraysToGenerics) {
                    $type = new GenericTypeNode(new IdentifierTypeNode('array'), [$type->type]);
                    $sortName = 'array';
                } elseif ($type->type instanceof IdentifierTypeNode) {
                    $sortName = $type->type->name;
                } else {
                    $sortName = 'array';
                }
            } elseif ($type instanceof ArrayShapeNode) {
                $sortName = 'array';
            } elseif ($type instanceof GenericTypeNode) {
                if (in_array($type->type->name, $shouldSort)) {
                    $sortName = $type->type->name;
                } else {
                    $sortName = 'array';
                }
            }

            if (!$sortName) {
                $unsortable[] = $type;
                continue;
            }

            if (in_array($sortName, $shouldSort, true)) {
                if ($type instanceof ArrayTypeNode) {
                    array_unshift($sortable[$sortName], $type);
                } else {
                    $sortable[$sortName][] = $type;
                }
            } else {
                $unsortable[] = $type;
            }
        }

        $sorted = [];
        array_walk($sortable, function ($types) use (&$sorted) {
            $sorted = array_merge($sorted, $types);
        });

        return $this->renderUnionTypes(array_merge($unsortable, $sorted));
    }

    /**
     * @param array<\PHPStan\PhpDocParser\Ast\Type\TypeNode> $typeNodes type nodes
     * @return string
     */
    protected function renderUnionTypes(array $typeNodes): string
    {
        return preg_replace(
            ['/ ([\|&]) /', '/<\(/', '/\)>/'],
            ['${1}', '<', '>'],
            implode('|', $typeNodes)
        );
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
