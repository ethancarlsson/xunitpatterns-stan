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

    /**
     * @return bool
     */
    public function isInTestMethod(): bool
    {
        $method = $this->scope->getFunction();
        $class = $this->scope->getClassReflection();

        if ($method === null || $class === null) {
            return false;
        }

        if (!$this->hasTestCaseParent($class)) {
            return false;
        }

        if ($this->hasTestPrependedToMethodName($method)) {
            return false;
        }

        return true;
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
        return preg_match('/^test\w+/', $method->getName()) === false;
    }
}