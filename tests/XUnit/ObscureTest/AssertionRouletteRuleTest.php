<?php

declare(strict_types=1);

namespace XUnitLint\Tests\XUnit\ObscureTest;

use PHPStan\ShouldNotHappenException;
use XUnitLint\Rule\ObscureTest\AssertionRouletteRule;
use XUnitLint\Tests\Mock\TestMethodScopeMockFactory;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPUnit\Framework\TestCase;

class AssertionRouletteRuleTest extends TestCase
{

    private AssertionRouletteRule $sut;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sut = new AssertionRouletteRule();
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function testProcessNode_withNonAssertMethod_returnsNoErrors(): void
    {
        $node = new StaticCall(new Name('notAnAssert'), 'notAnAssert', []);
        $scope = (new TestMethodScopeMockFactory())->createValidTestMethodScopeMock();

        $this->sut->processNode($node, $scope);
        $errors = $this->sut->processNode($node, $scope);
        self::assertEquals([], $errors);
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function testProcessNode_with1Method_returnsNoErrors(): void
    {
        $node = new StaticCall(new Name('assertEquals'), 'assertEquals', []);
        $scope = (new TestMethodScopeMockFactory())->createValidTestMethodScopeMock();

        $errors = $this->sut->processNode($node, $scope);

        self::assertEquals([], $errors);
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function testProcessNode_with4AssertMethods_returnsErrors(): void
    {
        $node = new StaticCall(new Name('assertEquals'), 'assertEquals', []);
        $scope = (new TestMethodScopeMockFactory())->createValidTestMethodScopeMock();

        $this->sut->processNode($node, $scope);
        $this->sut->processNode($node, $scope);
        $this->sut->processNode($node, $scope);
        $this->sut->processNode($node, $scope);
        $errors = $this->sut->processNode($node, $scope);

        self::assertCount(1, $errors);
    }
}
