<?php

namespace Symplify\PHP7_CodeSniffer\Ruleset\Tests;

use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\ByteOrderMarkSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineEndingsSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\UpperCaseConstantNameSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\DisallowShortOpenTagSniff;
use PHP_CodeSniffer\Standards\PSR1\Sniffs\Classes\ClassDeclarationSniff;
use PHP_CodeSniffer\Standards\PSR1\Sniffs\Files\SideEffectsSniff;
use PHP_CodeSniffer\Standards\PSR1\Sniffs\Methods\CamelCapsMethodNameSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Classes\ValidClassNameSniff;
use PHP_CodeSniffer\Util\Tokens;
use PHPUnit\Framework\TestCase;
use Symplify\PHP7_CodeSniffer\Ruleset\Routing\Router;
use Symplify\PHP7_CodeSniffer\Ruleset\Rule\ReferenceNormalizer;
use Symplify\PHP7_CodeSniffer\Ruleset\RulesetBuilder;
use Symplify\PHP7_CodeSniffer\Sniff\Finder\SniffClassFilter;
use Symplify\PHP7_CodeSniffer\Sniff\Finder\SniffClassRobotLoaderFactory;
use Symplify\PHP7_CodeSniffer\Sniff\Finder\SniffFinder;
use Symplify\PHP7_CodeSniffer\Standard\StandardFinder;
use Symplify\PHP7_CodeSniffer\Tests\Instantiator;

final class RulesetBuilderTest extends TestCase
{
    /**
     * @var RulesetBuilder
     */
    private $rulesetBuilder;

    protected function setUp()
    {
        new Tokens();
        $this->rulesetBuilder = Instantiator::createRulesetBuilder();
    }

    public function testBuildFromRulesetXml()
    {
        $ruleset = $this->rulesetBuilder->buildFromRulesetXml(
            __DIR__ . '/RulesetBuilderSource/ruleset.xml'
        );

        $this->assertInternalType('array', $ruleset);

        $this->assertSame([
            'Generic.Files.ByteOrderMark' => ByteOrderMarkSniff::class,
            'Generic.Files.LineEndings' => LineEndingsSniff::class,
            'Generic.NamingConventions.UpperCaseConstantName' => UpperCaseConstantNameSniff::class,
            'Generic.PHP.DisallowShortOpenTag.EchoFound' => DisallowShortOpenTagSniff::class,
            'PSR1.Classes.ClassDeclaration' => ClassDeclarationSniff::class,
            'PSR1.Files.SideEffects' => SideEffectsSniff::class,
            'PSR1.Methods.CamelCapsMethodName' => CamelCapsMethodNameSniff::class,
            'Squiz.Classes.ValidClassName' => ValidClassNameSniff::class,
        ], $ruleset);
    }
}
