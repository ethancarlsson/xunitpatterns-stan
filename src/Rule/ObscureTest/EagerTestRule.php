<?php

declare(strict_types=1);

namespace XUnitLint\Rule\ObscureTest;

use PHPStan\Rules\RuleError;
use XUnitLint\Facade\TestScope;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<StaticCall>
 */
class EagerTestRule implements Rule
{
    private const ASSERT = 'assert';
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
        $method = $scope->getFunction();
        if ($method === null) {
            return [];
        }

        if ((new TestScope($scope))->isInTestMethod() === false) {
            return [];
        }

        $this->resetCounterEachTestMethod($method->getName());

        $this->assertCounter++;

        if ($this->assertCounter <= 4) {
            return [];
        }

        /**
         * @var Identifier
         */
        $methodName = $node->name;

        if (!str_starts_with($methodName->toString(), self::ASSERT)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Eager test: You should ideally have only one assert per test method.'
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
}
