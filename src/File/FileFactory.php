<?php

/*
 * This file is part of Symplify
 * Copyright (c) 2016 Tomas Votruba (http://tomasvotruba.cz).
 */

namespace Symplify\PHP7_CodeSniffer\File;

use Symplify\PHP7_CodeSniffer\Configuration\Configuration;
use Symplify\PHP7_CodeSniffer\File\File;
use Symplify\PHP7_CodeSniffer\Fixer;
use Symplify\PHP7_CodeSniffer\ErrorDataCollector;
use Symplify\PHP7_CodeSniffer\Parser\EolCharDetector;
use Symplify\PHP7_CodeSniffer\Parser\FileToTokensParser;
use Symplify\PHP7_CodeSniffer\Ruleset;

final class FileFactory
{
    /**
     * @var Fixer
     */
    private $fixer;

    /**
     * @var ErrorDataCollector
     */
    private $reportCollector;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var FileToTokensParser
     */
    private $fileToTokenParser;
    
    /**
     * @var EolCharDetector
     */
    private $eolCharDetector;

    public function __construct(
        Fixer $fixer,
        ErrorDataCollector $reportCollector,
        Configuration $configuration,
        FileToTokensParser $fileToTokenParser,
        EolCharDetector $eolCharDetector
    ) {
        $this->fixer = $fixer;
        $this->reportCollector = $reportCollector;
        $this->configuration = $configuration;
        $this->fileToTokenParser = $fileToTokenParser;
        $this->eolCharDetector = $eolCharDetector;
    }

    public function create(string $filePath) : File
    {
        $tokens = $this->fileToTokenParser->parseFromFilePath($filePath);
        $eolChar = $this->eolCharDetector->detectForFilePath($filePath);

        return new File(
            $filePath,
            $tokens,
            $this->fixer,
            $this->reportCollector,
            $this->configuration->isFixer(),
            $eolChar
        );
    }
}