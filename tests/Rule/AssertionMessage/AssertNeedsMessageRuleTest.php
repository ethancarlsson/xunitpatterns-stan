<?php

declare(strict_types=1);

namespace XUnitLint\Tests\Rule\AssertionMessage;

use JetBrains\PhpStorm\Pure;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\ShouldNotHappenException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use XUnitLint\Rule\AssertionMessage\AssertNeedsMessageRule;
use XUnitLint\Rule\Service\AssertionMethodService;
use XUnitLint\Tests\Mock\FakeExpr;
use XUnitLint\Tests\Mock\TestMethodScopeMockFactory;

class AssertNeedsMessageRuleTest extends TestCase
{

    private AssertNeedsMessageRule $sut;
    /**
     * @var MockObject
     */
    private mixed $mockedAssertionMethodService;

    protected function setUp(): void
    {
        $this->mockedAssertionMethodService = $this->createMock(AssertionMethodService::class);

        $this->sut = new AssertNeedsMessageRule($this->mockedAssertionMethodService);

        parent::setUp();
    }

    /**
     * @return iterable<string, array{name: string, args: array<Arg>, expectedErrorCount: int}>
     */
    public function provideAssertionMethods(): iterable
    {
        yield 'assertTrue with no message' => [
            'name' => 'assertTrue',
            'args' => [$this->createArg()],
            'expectedErrorCount' => 1,
        ];

        yield 'assertTrue with message' => [
            'name' => 'assertTrue',
            'args' => [$this->createArg(), $this->createArg()],
            'expectedErrorCount' => 0,
        ];

        yield 'assertFalse with no message' => [
            'name' => 'assertFalse',
            'args' => [$this->createArg()],
            'expectedErrorCount' => 1,
        ];
    }

    /**
     * @dataProvider provideAssertionMethods
     * @param string $name
     * @param Arg[] $args
     * @param int $expectedErrorCount
     * @throws ShouldNotHappenException
     */
    public function testProcessNode(string $name, array $args, int $expectedErrorCount): void
    {
        $node = new StaticCall(new Name($name), $name, $args);
        $scope = (new TestMethodScopeMockFactory())->createValidTestMethodScopeMock();
        $this->mockedAssertionMethodService->method('isAnAssertionMethod')->willReturn(true);

        $errors = $this->sut->processNode($node, $scope);
        $argNum = \count($args);

        self::assertCount(
            $expectedErrorCount,
            $errors,
            "Failed asserting $name with $argNum arguments produces $expectedErrorCount errors"
        );
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function testProcessNode_withNotAssertMethod_returnsNoErrors(): void
    {
        $node = new StaticCall(new Name('assertTrue'), 'assertTrue', []);
        $scope = (new TestMethodScopeMockFactory())->createValidTestMethodScopeMock();

        $this->mockedAssertionMethodService->method('isAnAssertionMethod')->willReturn(false);

        $errors = $this->sut->processNode($node, $scope);
        self::assertEmpty($errors, 'Failed asserting that a non assert method gives no errors.');
    }

    #[Pure] private function createArg(): Arg
    {
        return new Arg(new FakeExpr());
    }
}
