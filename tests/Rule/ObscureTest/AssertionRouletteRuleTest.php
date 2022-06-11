<?php

declare(strict_types=1);

namespace XUnitLint\Tests\Rule\ObscureTest;

use PHPStan\ShouldNotHappenException;
use PHPUnit\Framework\MockObject\MockObject;
use XUnitLint\Rule\ObscureTest\AssertionRouletteRule;
use XUnitLint\Rule\Service\AssertionMethodService;
use XUnitLint\Tests\Mock\TestMethodScopeMockFactory;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPUnit\Framework\TestCase;

class AssertionRouletteRuleTest extends TestCase
{

    private AssertionRouletteRule $sut;
    /**
     * @var MockObject
     */
    private mixed $assertionMethodService;

    protected function setUp(): void
    {

        $this->assertionMethodService = $this->createMock(AssertionMethodService::class);
        $this->sut = new AssertionRouletteRule($this->assertionMethodService);
        parent::setUp();
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function testProcessNode_withNonAssertMethod5Times_returnsNoErrors(): void
    {
        $node = new StaticCall(new Name('notAnAssert'), 'notAnAssert', []);
        $scope = (new TestMethodScopeMockFactory())->createValidTestMethodScopeMock();

        $this->assertionMethodService->method('isAnAssertionMethod')->willReturn(false);

        $this->sut->processNode($node, $scope);
        $this->sut->processNode($node, $scope);
        $this->sut->processNode($node, $scope);
        $this->sut->processNode($node, $scope);
        $this->sut->processNode($node, $scope);
        $this->sut->processNode($node, $scope);
        $errors = $this->sut->processNode($node, $scope);
        self::assertEquals([], $errors);
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function testProcessNode_with1TestMethod_returnsNoErrors(): void
    {
        $node = new StaticCall(new Name('assertEquals'), 'assertEquals', []);
        $scope = (new TestMethodScopeMockFactory())->createValidTestMethodScopeMock();

        $this->assertionMethodService->method('isAnAssertionMethod')->willReturn(true);

        $errors = $this->sut->processNode($node, $scope);

        self::assertEquals([], $errors);
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function testProcessNode_with5AssertMethods_returnsErrors(): void
    {
        $node = new StaticCall(new Name('assertEquals'), 'assertEquals', []);
        $scope = (new TestMethodScopeMockFactory())->createValidTestMethodScopeMock();

        $this->assertionMethodService->method('isAnAssertionMethod')->willReturn(true);

        $this->sut->processNode($node, $scope);
        $this->sut->processNode($node, $scope);
        $this->sut->processNode($node, $scope);
        $this->sut->processNode($node, $scope);

        $errors = $this->sut->processNode($node, $scope);

        self::assertCount(1, $errors);
    }
}
