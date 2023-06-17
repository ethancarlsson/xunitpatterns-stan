<?php

declare(strict_types=1);

namespace XUnitLint\Tests\Mock;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TestMethodScopeMockFactory extends TestCase
{
    /**
     * @return MockObject&ClassReflection
     */
    public function createDefaultTestClassScopeMock(): MockObject
    {
        $mockClassReflections = $this->createMock(ClassReflection::class);
        $testClass = $this->createMock(ClassReflection::class);
        $testClass->method('getName')->willReturn(TestCase::class);
        $mockClassReflections->method('getParents')->willReturn([$testClass]);

        return $mockClassReflections;
    }

    public function createDefaultTestMethodScopeMock(?string $name = 'testValidTestMethod'): ClassReflection|MockObject
    {
        $function = $this->createMock(MethodReflection::class);
        $function->method('getName')->willReturn($name);

        return $function;
    }

    public function createValidTestMethodScopeMock(): Scope
    {
        $scope = $this->createMock(Scope::class);
        $scope->method('getClassReflection')->willReturn($this->createDefaultTestClassScopeMock());
        $scope->method('getFunction')->willReturn($this->createDefaultTestMethodScopeMock());

        return $scope;
    }
}