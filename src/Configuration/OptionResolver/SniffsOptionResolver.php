<?php

/*
 * This file is part of Symplify
 * Copyright (c) 2016 Tomas Votruba (http://tomasvotruba.cz).
 */

namespace Symplify\PHP7_CodeSniffer\Configuration\OptionResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symplify\PHP7_CodeSniffer\Configuration\ValueNormalizer;
use Symplify\PHP7_CodeSniffer\Contract\Configuration\OptionResolver\OptionResolverInterface;
use Symplify\PHP7_CodeSniffer\Exception\Configuration\InvalidSniffCodeException;

final class SniffsOptionResolver implements OptionResolverInterface
{
    /**
     * @var string
     */
    const NAME = 'sniffs';

    public function getName() : string
    {
        return self::NAME;
    }

    public function resolve(array $value) : array
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined(self::NAME);
        $this->setSniffsAllowedValues($optionsResolver);

        $values = $optionsResolver->resolve([
            self::NAME => $value
        ]);

        return $values[self::NAME];
    }

    private function setSniffsAllowedValues(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setAllowedValues(self::NAME, function (array $sniffs) {
            $sniffs = ValueNormalizer::normalizeCommaSeparatedValues($sniffs);

            foreach ($sniffs as $sniff) {
                if (substr_count($sniff, '.') !== 2) {
                    throw new InvalidSniffCodeException(sprintf(
                        'The specified sniff code "%s" is invalid.' .
                        PHP_EOL .
                        'Correct format is "StandardName.Category.SniffName".',
                        $sniff
                    ));
                }
            }

            return true;
        });
    }
}
