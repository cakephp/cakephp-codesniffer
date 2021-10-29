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
 * Verifies order of types in type hints. Also removes duplicates.
 */
class TypeHintSniff implements Sniff
{
    /**
     * @var bool
     */
    public $convertArraysToGenerics = true;

    /**
     * @var array<string>
     */
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
     * Highest/First element will be first in list of param or return tag.
     *
     * @var array<string>
     */
    protected static $sortMap = [
        '\\Closure',
        '\\Traversable',
        '\\ArrayAccess',
        '\\ArrayObject',
        '\\Stringable',
        '\\Generator',
        'mixed',
        'callable',
        'resource',
        'object',
        'iterable',
        'list',
        'array',
        'callable-string',
        'class-string',
        'interface-string',
        'scalar',
        'string',
        'float',
        'int',
        'bool',
        'true',
        'false',
        'null',
        'void',
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @inheritDoc
     */
    public function register()
    {
        return [T_DOC_COMMENT_OPEN_TAG];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @inheritDoc
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
                !in_array($tokens[$tag]['content'], static::$typeHintTags, true)
            ) {
                continue;
            }

            $tagComment = $phpcsFile->fixer->getTokenContent($tag + 2);
            $valueNode = static::getValueNode($tokens[$tag]['content'], $tagComment);
            if ($valueNode instanceof InvalidTagValueNode) {
                continue;
            }

            $hasUnion = false;
            /** @var \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode $valueNode */
            if ($valueNode->type instanceof UnionTypeNode) {
                $types = $valueNode->type->types;
                $hasUnion = true;
            } elseif ($valueNode->type instanceof ArrayTypeNode) {
                $types = [$valueNode->type];
            } else {
                continue;
            }

            $originalTypeHint = $this->renderUnionTypes($types);
            $sortedTypeHint = $this->getSortedTypeHint($types, $hasUnion);
            if ($sortedTypeHint === $originalTypeHint) {
                continue;
            }

            $fix = $phpcsFile->addFixableError(
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
     * @param array<\PHPStan\PhpDocParser\Ast\Type\TypeNode> $types node types
     * @return string
     */
    protected function getSortedTypeHint(array $types, bool $hasUnion): string
    {
        $sortable = array_fill_keys(static::$sortMap, []);
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
                if (!$this->convertArraysToGenerics) {
                    $sortName = $type->type->name;
                } elseif (!$this->isComplexObjectCollection($types, $hasUnion)) {
                    $type = new GenericTypeNode(new IdentifierTypeNode('array'), [$type->type]);
                    $sortName = 'array';
                } elseif ($type->type instanceof IdentifierTypeNode) {
                    $sortName = 'array';
                } else {
                    $sortName = 'array';
                }
            } elseif ($type instanceof ArrayShapeNode) {
                $sortName = 'array';
            } elseif ($type instanceof GenericTypeNode) {
                if (in_array($type->type->name, static::$sortMap)) {
                    $sortName = $type->type->name;
                } else {
                    $sortName = 'array';
                }
            }

            if (!$sortName) {
                $unsortable[] = $type;

                continue;
            }

            if (in_array($sortName, static::$sortMap, true)) {
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
        array_walk($sortable, function ($types) use (&$sorted): void {
            $sorted = array_merge($sorted, $types);
        });

        $types = array_merge($unsortable, $sorted);
        $types = $this->makeUnique($types);

        return $this->renderUnionTypes($types);
    }

    /**
     * @param array<\PHPStan\PhpDocParser\Ast\Type\TypeNode> $typeNodes type nodes
     * @return string
     */
    protected function renderUnionTypes(array $typeNodes): string
    {
        return (string)preg_replace(
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
    protected static function getValueNode(string $tagName, string $tagComment): PhpDocTagValueNode
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

    /**
     * @param array<\PHPStan\PhpDocParser\Ast\Type\TypeNode> $types
     * @return array<\PHPStan\PhpDocParser\Ast\Type\TypeNode>
     */
    protected function makeUnique(array $types): array
    {
        $typesAsString = [];

        foreach ($types as $key => $type) {
            $type = (string)$type;
            if (in_array($type, $typesAsString, true)) {
                unset($types[$key]);

                continue;
            }
            $typesAsString[] = $type;
        }

        return $types;
    }

    /**
     * @param array<\PHPStan\PhpDocParser\Ast\Type\TypeNode> $types
     * @return bool
     */
    protected function isComplexObjectCollection(array $types, bool $isUnion): bool
    {
        if (!$isUnion) {
        }

        foreach ($types as $type) {
            if (!$type instanceof IdentifierTypeNode) {
                continue;
            }

            if (strpos((string)$type, '\\') === 0) {
                return true;
            }
        }

        return false;
    }
}
