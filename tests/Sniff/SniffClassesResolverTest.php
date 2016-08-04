<?php

namespace Symplify\PHP7_CodeSniffer\Tests\Sniff;

use PHP_CodeSniffer\Util\Tokens;
use PHPUnit\Framework\TestCase;
use Symplify\PHP7_CodeSniffer\Sniff\SniffClassesResolver;
use Symplify\PHP7_CodeSniffer\Tests\Instantiator;

final class SniffClassesResolverTest extends TestCase
{
    /**
     * @var SniffClassesResolver
     */
    private $sniffClassesResolver;

    protected function setUp()
    {
        new Tokens();

        $this->sniffClassesResolver = new SniffClassesResolver(
            Instantiator::createConfigurationResolver(),
            Instantiator::createRulesetBuilder()
        );
    }

    /**
     * @dataProvider provideDataForResolver()
     */
    public function testResolveFromStandardsAndSniffs(
        array $standards,
        array $extraSniffs,
        array $excludedSniffs,
        int $sniffCount
    ) {
        $sniffs = $this->sniffClassesResolver->resolveFromStandardsAndSniffs(
            $standards,
            $extraSniffs,
            $excludedSniffs
        );

        $this->assertCount($sniffCount, $sniffs);
    }

    public function provideDataForResolver() : array
    {
        return [
            [
                [], [], [], 0
            ], [
                ['PSR2'], [], [], 43
            ], [
                ['PSR2'], ['PEAR.Commenting.ClassComment'], [], 44
            ], [
                ['PSR2'],
                ['PEAR.Commenting.ClassComment'],
                ['PEAR.Commenting.ClassComment', 'PSR2.Namespaces.UseDeclaration'],
                42
            ],
        ];
    }
}