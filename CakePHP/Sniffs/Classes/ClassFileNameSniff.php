<?php

namespace CakePHP\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Tests that the file name and the name of the class contained within the file
 * match.
 *
 * @category PHP
 * @package PHP_CodeSniffer
 * @author Greg Sherwood <gsherwood@squiz.net>
 * @author Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version Release: @package_version@
 * @link http://pear.php.net/package/PHP_CodeSniffer
 */
class ClassFileNameSniff implements Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_CLASS,
            T_INTERFACE,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $fullPath = basename($phpcsFile->getFilename());
        $fileName = substr($fullPath, 0, strrpos($fullPath, '.'));
        if ($fileName === '') {
            // No filename probably means STDIN, so we can't do this check.
            return;
        }

        $tokens = $phpcsFile->getTokens();

        $previous = $phpcsFile->findPrevious([T_CLASS, T_INTERFACE], $stackPtr - 1);
        if ($previous) {
            // Probably more than a single declaration per file, we only check first one then.
            return;
        }

        $decName = $phpcsFile->findNext(T_STRING, $stackPtr);

        if ($tokens[$decName]['content'] === $fileName) {
            return;
        }

        $error = '%s name doesn\'t match filename; expected "%s %s"';
        $data = [
            ucfirst($tokens[$stackPtr]['content']),
            $tokens[$stackPtr]['content'],
            $fileName,
        ];
        $phpcsFile->addError($error, $stackPtr, 'NoMatch', $data);
    }
}
