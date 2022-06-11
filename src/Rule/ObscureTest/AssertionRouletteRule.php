<?php

declare(strict_types=1);

namespace XUnitLint\Rule\ObscureTest;

use PhpParser\Node\Expr\CallLike;
use PHPStan\Rules\RuleError;
use XUnitLint\Answerer\NamespaceAnswererImp;
use XUnitLint\Facade\TestScope;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use XUnitLint\Rule\Service\AssertionMethodService;

/**
 * @implements Rule<CallLike>
 */
class AssertionRouletteRule implements Rule
{
    private AssertionMethodService $assertionMethodService;

    public function __construct(AssertionMethodService $assertionMethodService)
    {
        $this->assertionMethodService = $assertionMethodService;
    }

    private string $currentMethodName = '0';
    private int $assertCounter = 0;

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
     * @param Scope $scope
     * @return RuleError[]
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {

        $namespace = $this->getNamespaceAnswererImp($scope);


        if ($this->assertionMethodService->isAnAssertionMethod($node, $namespace) === false) {
            return [];
        }

        if ((new TestScope($scope))->isInTestClass() === false) {
            return [];
        }


        $method = $scope->getFunction();

        if ($method === null) {
            return [];
        }

        $this->resetCounterEachTestMethod($method->getName());

        $this->assertCounter++;

        if ($this->assertCounter < 5) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Assertion roulette: You should ideally have only one assert per test method.'
                . ' Consider splitting this test into multiple'
                . ' tests, or if the asserts are related create a custom assertion.'
            )->build(),
        ];
    }

    /**
     * @param string $methodName
     */
    private function resetCounterEachTestMethod(string $methodName): void
    {
        if ($this->currentMethodName !== $methodName) {
            $this->assertCounter = 0;
            $this->currentMethodName = $methodName;
        }
    }

    private function getNamespaceAnswererImp(Scope $scope): NamespaceAnswererImp
    {
        return new NamespaceAnswererImp(
            $scope->getClassReflection()
                ?->getParentClass()
                ?->getNativeReflection()
                ?->getNamespaceName()
            ?? ''
        );
    }
}
