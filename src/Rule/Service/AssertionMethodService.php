<?php

declare(strict_types=1);

namespace XUnitLint\Rule\Service;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\ReflectionProvider;
use PHPUnit\Framework\ExpectationFailedException;
use XUnitLint\Answerer\NamespaceAnswererImp;

class AssertionMethodService
{

    private ReflectionProvider $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function isAnAssertionMethod(MethodCall|StaticCall $node, NamespaceAnswererImp $namespace): bool
    {
        return $this->canThrowExpectationFailedException($node, $namespace);
    }

    /**
     * @param MethodCall|StaticCall $node
     * @param NamespaceAnswererImp $namespace
     * @return bool
     */
    private function canThrowExpectationFailedException(
        MethodCall|StaticCall $node,
        NamespaceAnswererImp $namespace
    ): bool {
        /**
         *
         * @var Node\Identifier
         */
        $name = $node->name;
        $nodeName = new Node\Name($name->toString());

        if ($this->reflectionProvider->hasFunction($nodeName, $namespace) === false) {
            return false;
        }

        $functionReflection = $this->reflectionProvider
            ->getFunction($nodeName, $namespace);
        $throwTypes = $functionReflection
            ->getThrowType();


        $referencedClasses = $throwTypes?->getReferencedClasses() ?? [];

        return $this->includesExpectationFailed($referencedClasses);
    }

    /**
     * @param string[] $referencedClasses
     */
    private function includesExpectationFailed(array $referencedClasses): bool
    {
        foreach ($referencedClasses as $throwType) {
            if ($throwType === ExpectationFailedException::class) {
                return true;
            }
        }
        return false;
    }
}