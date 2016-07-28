<?php

/*
 * This file is part of Symplify
 * Copyright (c) 2016 Tomas Votruba (http://tomasvotruba.cz).
 */

namespace Symplify\PHP7_CodeSniffer\Ruleset;

use SimpleXMLElement;
use Symplify\PHP7_CodeSniffer\Ruleset\Rule\ReferenceNormalizer;
use Symplify\PHP7_CodeSniffer\SniffFinder\SniffFinder;
use Symplify\PHP7_CodeSniffer\Standard\StandardFinder;

final class RulesetBuilder
{
    /**
     * @var SniffFinder
     */
    private $sniffFinder;

    /**
     * @var ReferenceNormalizer
     */
    private $ruleReferenceNormalizer;

    /**
     * @var array
     */
    private $ruleset = [];

    /**
     * @var array
     */
    private $includedSniffs = [];

    /**
     * @var array
     */
    private $excludedSniffs = [];

    /**
     * @var StandardFinder
     */
    private $standardFinder;

    public function __construct(
        SniffFinder $sniffFinder,
        StandardFinder $standardFinder,
        ReferenceNormalizer $ruleReferenceNormalizer
    ) {
        $this->sniffFinder = $sniffFinder;
        $this->standardFinder = $standardFinder;
        $this->ruleReferenceNormalizer = $ruleReferenceNormalizer;
    }

    public function buildFromRulesetXml(string $rulesetXmlFile) : array
    {
        $this->cleanCache();

        $rulesetXml = simplexml_load_file($rulesetXmlFile);
        foreach ($rulesetXml->rule as $rule) {
            if (isset($rule['ref']) === false) {
                continue;
            }

            $expandedSniffs = $this->normalizeReference($rule['ref']);
            $newSniffs = array_diff($expandedSniffs, $this->includedSniffs);

            $this->includedSniffs = array_merge($this->includedSniffs, $expandedSniffs);

            $this->processExcludedRules($rule);

            $this->processRule($rule, $newSniffs);
        }

        $ownSniffs = $this->getOwnSniffsFromRuleset($rulesetXmlFile);

        $this->includedSniffs = array_unique(array_merge($ownSniffs, $this->includedSniffs));
        $this->excludedSniffs = array_unique($this->excludedSniffs);

        return $this->filterOutExcludedSniffs();
    }

    public function getRuleset() : array
    {
        return $this->ruleset;
    }

    /**
     * Processes a rule from a ruleset XML file, overriding built-in defaults.
     */
    private function processRule(SimpleXMLElement $rule, array $newSniffs)
    {
        $ref  = (string) $rule['ref'];
        $todo = [$ref];

        $parts = explode('.', $ref);
        if (count($parts) <= 2) {
            // We are processing a standard or a category of sniffs.
            foreach ($newSniffs as $sniffFile) {
                $parts = explode(DIRECTORY_SEPARATOR, $sniffFile);
                $sniffName = array_pop($parts);
                $sniffCategory = array_pop($parts);
                array_pop($parts);
                $sniffStandard = array_pop($parts);
                $todo[] = $sniffStandard.'.'.$sniffCategory.'.'.substr($sniffName, 0, -9);
            }
        }

        foreach ($todo as $code) {
            // Custom properties.
            if (isset($rule->properties) === true) {
                foreach ($rule->properties->property as $prop) {
                    if (isset($this->ruleset[$code]) === false) {
                        $this->ruleset[$code] = [
                            'properties' => [],
                        ];
                    } else if (isset($this->ruleset[$code]['properties']) === false) {
                        $this->ruleset[$code]['properties'] = [];
                    }

                    $name = (string) $prop['name'];
                    if (isset($prop['type']) === true
                        && (string) $prop['type'] === 'array'
                    ) {
                        $value  = (string) $prop['value'];
                        $values = [];
                        foreach (explode(',', $value) as $val) {
                            $v = '';

                            list($k,$v) = explode('=>', $val.'=>');
                            if ($v !== '') {
                                $values[$k] = $v;
                            } else {
                                $values[] = $k;
                            }
                        }

                        $this->ruleset[$code]['properties'][$name] = $values;
                    } else {
                        $this->ruleset[$code]['properties'][$name] = (string) $prop['value'];
                    }
                }
            }
        }
    }
    
    private function normalizeReference(string $reference)
    {
        if ($this->ruleReferenceNormalizer->isRulesetReference($reference)) {
            return $this->buildFromRulesetXml($reference);
        }

        if ($this->ruleReferenceNormalizer->isStandardReference($reference)) {
            $ruleset = $this->standardFinder->getRulesetPathForStandardName($reference);
            return $this->buildFromRulesetXml($ruleset);
        }

        return $this->ruleReferenceNormalizer->normalize($reference);
    }

    private function cleanCache()
    {
        $this->includedSniffs = [];
        $this->excludedSniffs = [];
    }

    /**
     * @return string[]
     */
    private function getOwnSniffsFromRuleset(string $rulesetXml) : array
    {
        $rulesetDir = dirname($rulesetXml);
        $sniffDir = $rulesetDir.DIRECTORY_SEPARATOR.'Sniffs';
        if (is_dir($sniffDir)) {
            return $this->sniffFinder->findAllSniffClassesInDirectory($sniffDir);
        }

        return [];
    }

    private function processExcludedRules(SimpleXMLElement $rule)
    {
        if (isset($rule->exclude) === true) {
            foreach ($rule->exclude as $exclude) {
                $this->excludedSniffs = array_merge(
                    $this->excludedSniffs,
                    $this->normalizeReference($exclude['name'])
                );
            }
        }
    }

    private function filterOutExcludedSniffs() : array
    {
        $sniffs = [];
        foreach ($this->includedSniffs as $sniffCode => $sniffClass) {
            if (!in_array($sniffCode, $this->excludedSniffs)) {
                $sniffs[$sniffCode] = $sniffClass;
            }
        }

        return $sniffs;
    }
}