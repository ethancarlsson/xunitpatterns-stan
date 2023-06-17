<?php

declare(strict_types=1);

namespace XUnitLint\Rule\Conditional;

use XUnitLint\ErrorBuilder\ConditionalLogicErrorBuilder;
use XUnitLint\Facade\TestScope;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<Node>
 */
abstract class ConditionalTestLogicRule implements Rule
{
    protected string $message;

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $testScope = new TestScope($scope);


        if (!$testScope->isInTestMethod()) {
            return [];
        }

        return [
            ConditionalLogicErrorBuilder::message(
                $this->message
            )->build(),
        ];
    }
}
