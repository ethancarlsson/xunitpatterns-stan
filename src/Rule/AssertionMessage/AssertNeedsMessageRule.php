<?php

declare(strict_types=1);

namespace XUnitLint\Rule\AssertionMessage;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PhpParser\Node;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use XUnitLint\Answerer\NamespaceAnswererImp;
use XUnitLint\Rule\Service\AssertionMethodService;

/**
 * @implements  Rule<StaticCall>
 */
class AssertNeedsMessageRule implements Rule
{

    private const ASSERT_TRUE = 'assertTrue';
    private const ASSERT_FALSE = 'assertFalse';

    private const TARGETED_ASSERT_METHODS = [self::ASSERT_TRUE, self::ASSERT_FALSE];
    private AssertionMethodService $assertionMethodService;

    public function __construct(AssertionMethodService $assertionMethodService)
    {
        $this->assertionMethodService = $assertionMethodService;
    }

    /**
     * @inheritDoc
     */
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
        return $this->getErrors($node);
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

    /**
     * @param StaticCall $node
     * @return RuleError[]
     * @throws ShouldNotHappenException
     */
    private function getErrors(StaticCall $node): array
    {
        /**
         * @var Node\Identifier
         */
        $nodeIdentifier = $node->name;

        if (\in_array(
                $nodeIdentifier->name,
                self::TARGETED_ASSERT_METHODS
            ) === false) {
            return [];
        }

        if (\count($node->args) === 2) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                "$nodeIdentifier->name has an ambiguous error message, "
                . 'consider using a custom message to help future debugging efforts.'
            )->build(),
        ];
    }
}