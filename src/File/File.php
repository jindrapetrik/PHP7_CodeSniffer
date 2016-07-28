<?php

/*
 * This file is part of Symplify
 * Copyright (c) 2016 Tomas Votruba (http://tomasvotruba.cz).
 */

namespace Symplify\PHP7_CodeSniffer\File;

use PHP_CodeSniffer\Files\File as BaseFile;
use Symplify\PHP7_CodeSniffer\Contract\File\FileInterface;
use Symplify\PHP7_CodeSniffer\Fixer;
use Symplify\PHP7_CodeSniffer\ErrorDataCollector;

final class File extends BaseFile implements FileInterface
{
    /**
     * @var string
     */
    public $tokenizerType = 'PHP';

    /**
     * @var Fixer
     */
    public $fixer;

    /**
     * @var ErrorDataCollector
     */
    private $reportCollector;

    /**
     * @var bool
     */
    private $isFixer;

    public function __construct(
        string $path,
        array $tokens,
        Fixer $fixer,
        ErrorDataCollector $reportCollector,
        bool $isFixer,
        string $eolChar
    ) {
        $this->path = $path;
        $this->tokens = $tokens;
        $this->fixer = $fixer;
        $this->reportCollector = $reportCollector;
        $this->eolChar = $eolChar;

        $this->numTokens = count($this->tokens);
        $this->content = file_get_contents($path);
        $this->isFixer = $isFixer;
    }

    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        throw new \Exception('Not implemented, nor needed to be public. File is already parsed on __construct.');
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        throw new \Exception('Not implemented, nor needed to be public. Use external processing.');
    }

    /**
     * {@inheritdoc}
     */
    public function addFixableError($error, $stackPtr, $code, $data = [], $severity = 0)
    {
        $this->addError($error, $stackPtr, $code, $data, $severity, true);
        return $this->isFixer;
    }

    /**
     * {@inheritdoc}
     */
    protected function addMessage($error, $message, $line, $column, $code, $data, $severity, $isFixable = false) : bool
    {
        if (!$error) { // skip warnings
            return false;
        }

        $this->reportCollector->addErrorMessage($this->path, $message, $line, $code, $data, $isFixable);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent() : string
    {
        return $this->getTokensAsString(0, count($this->tokens));
    }
}