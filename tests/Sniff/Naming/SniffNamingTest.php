<?php

namespace Symplify\PHP7_CodeSniffer\Tests\Sniff\Naming;

use PHP_CodeSniffer\Standards\PSR2\Sniffs\Classes\ClassDeclarationSniff;
use PHPUnit\Framework\TestCase;
use Symplify\PHP7_CodeSniffer\Sniff\Naming\SniffNaming;

class SniffNamingTest extends TestCase
{
    /**
     * @expectedException \Symplify\PHP7_CodeSniffer\Exception\Sniff\Naming\InvalidSniffCodeException
     */
    public function testIncorrectCode()
    {
        SniffNaming::guessClassByCode('Standard.Category');
    }

    /**
     * @expectedException \Symplify\PHP7_CodeSniffer\Exception\Sniff\Naming\SniffClassCouldNotBeFoundException
     */
    public function testMissingClass()
    {
        SniffNaming::guessClassByCode('Standard.Category.SniffName');
    }

    /**
     * @expectedException \Symplify\PHP7_CodeSniffer\Exception\Sniff\Naming\InvalidSniffClassException
     */
    public function testIncorrectClass()
    {
        SniffNaming::guessCodeByClass('SomeClass');
    }

    public function testGuessSniffCodeByClassName()
    {
        $sniffName = SniffNaming::guessCodeByClass(ClassDeclarationSniff::class);
        $this->assertSame('PSR2.Classes.ClassDeclaration', $sniffName);
    }
}
