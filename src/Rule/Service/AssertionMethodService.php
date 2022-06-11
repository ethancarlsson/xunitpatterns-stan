<?php

declare(strict_types=1);

namespace XUnitLint\Rule\Service;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\ReflectionProvider;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use XUnitLint\Answerer\NamespaceAnswererImp;

class AssertionMethodService
{

    private const ASSERT_COUNT = 'assertCount';
    private const ASSERT_NOT_COUNT = 'assertNotCount';
    private ReflectionProvider $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function isAnAssertionMethod(MethodCall|StaticCall $node, NamespaceAnswererImp $namespace): bool
    {
        return $this->canThrowExpectationFailedException($node, $namespace)
            || $this->isAssertCount($node, $namespace);
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
        $name = $this->getIdentifierFromNode($node);
        $nodeName = new Node\Name($name->toString());

        if ($this->reflectionHasFunction($name, $namespace) === false) {
            return false;
        }

        $possibleExceptions = $this->getPossibleExceptions($nodeName, $namespace);

        return $this->arrayIncludes($possibleExceptions, ExpectationFailedException::class);
    }

    /**
     * @param string[] $referencedClasses
     */
    private function arrayIncludes(array $referencedClasses, string $exceptionClass): bool
    {
        foreach ($referencedClasses as $throwType) {
            if ($throwType === $exceptionClass) {
                return true;
            }
        }
        return false;
    }

    private function isAssertCount(MethodCall|StaticCall $node, NamespaceAnswererImp $namespace): bool
    {
        $identifier = $this->getIdentifierFromNode($node);
        if ($this->reflectionHasFunction($identifier, $namespace) === false) {
            return false;
        }

        $name = new Node\Name($identifier->toString());

        $possibleExceptions = $this->getPossibleExceptions($name, $namespace);

        $nameStr = $identifier->toString();
        return ($nameStr === self::ASSERT_COUNT
                || $nameStr === self::ASSERT_NOT_COUNT)
            && $this->arrayIncludes($possibleExceptions, Exception::class);
    }

    private function reflectionHasFunction(Node\Identifier $name, NamespaceAnswererImp $namespace): bool
    {
        $nodeName = new Node\Name($name->toString());
        return $this->reflectionProvider->hasFunction($nodeName, $namespace);
    }

    /**
     * @param StaticCall|MethodCall $node
     * @return Node\Identifier
     */
    private function getIdentifierFromNode(StaticCall|MethodCall $node): Node\Identifier
    {
        /**
         * @var Node\Identifier
         */
        return $node->name;
    }

    /**
     * @param Node\Name $nodeName
     * @param NamespaceAnswererImp $namespace
     * @return string[]
     */
    private function getPossibleExceptions(Node\Name $nodeName, NamespaceAnswererImp $namespace): array
    {
        $functionReflection = $this->reflectionProvider
            ->getFunction($nodeName, $namespace);
        $throwTypes = $functionReflection
            ->getThrowType();

        return $throwTypes?->getReferencedClasses() ?? [];
    }

}