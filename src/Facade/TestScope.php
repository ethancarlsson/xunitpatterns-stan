<?php

declare(strict_types=1);

namespace XUnitLint\Facade;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPUnit\Framework\TestCase;

class TestScope
{
    public function __construct(private Scope $scope)
    {
    }

    public function isInTestClass(): bool
    {
        $class = $this->scope->getClassReflection();
        if ($class === null) {
            return false;
        }
        return $this->hasTestCaseParent($class);
    }

    /**
     * @return bool
     */
    public function isInTestMethod(): bool
    {
        $method = $this->scope->getFunction();

        if ($this->isInTestClass() === false) {
            return false;
        }

        if ($method === null) {
           return false;
        }

        return $this->hasTestPrependedToMethodName($method);
    }

    private function hasTestCaseParent(ClassReflection $class): bool
    {
        foreach ($class->getParents() as $parent) {
            if ($parent->getName() === TestCase::class) {
                return true;
            }
        }
        return false;
    }

    private function hasTestPrependedToMethodName(MethodReflection|FunctionReflection $method): bool
    {
        return (bool)preg_match('/^test\w+/', $method->getName());
    }
}