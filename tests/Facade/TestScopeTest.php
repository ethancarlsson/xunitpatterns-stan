<?php

declare(strict_types=1);

namespace XUnitLint\Tests\Facade;

use XUnitLint\Facade\TestScope;
use XUnitLint\Tests\Mock\TestMethodScopeMockFactory;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TestScopeTest extends TestCase
{
    /**
     * @var mixed|Scope|MockObject
     */
    private mixed $scope;
    private TestScope $sut;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scope = $this->createMock(Scope::class);
        $this->sut = new TestScope($this->scope);
    }

    public function testIsInTestMethod_outsideFunction_returnsFalse(): void
    {
        $this->scope->method('getFunction')->willReturn(null);
        self::assertFalse($this->sut->isInTestMethod());
    }

    public function testIsInTestMethod_outsideClass_returnsFalse(): void
    {
        $this->scope->method('getClassReflection')->willReturn(null);
        self::assertFalse($this->sut->isInTestMethod());
    }

    public function testIsInTestMethod_withRegularFunctionNotNamedTest_returnsFalse(): void
    {
        $mockClassReflection = $this->createMock(ClassReflection::class);
        $this->scope->method('getClassReflection')->willReturn($mockClassReflection);
        $this->scope->method('getFunction')->willReturn($this->createMockFunctionReflection('notATestFunction'));
        self::assertFalse($this->sut->isInTestMethod());
    }

    public function testIsInTestMethod_withFunctionNamedTest_returnsTrue(): void
    {
        $mockClassReflection = $this->createMockClassReflectionWithTestSubClass();
        $this->scope->method('getClassReflection')->willReturn($mockClassReflection);
        $this->scope->method('getFunction')->willReturn($this->createMockFunctionReflection('testFunction'));
        self::assertTrue($this->sut->isInTestMethod());
    }

    public function testIsInTestMethod_withFunctionNamedTestNotInTestClass_returnsFalse(): void
    {
        $mockClassReflection = $this->createMock(ClassReflection::class);
        $mockClassReflection->method('getParents')->willReturn([]);

        $this->scope->method('getClassReflection')->willReturn($mockClassReflection);

        $this->scope->method('getFunction')->willReturn($this->createMockFunctionReflection('testFunction'));
        self::assertFalse($this->sut->isInTestMethod());
    }

    public function testIsInTestMethod_withFunctionNotNamedTestInTestClassWithTestDocBlock_returnsTrue(): void
    {
        $mockClassReflection = $this->createMock(ClassReflection::class);
        $mockClassReflection->method('getParents')->willReturn([]);

        $this->scope->method('getClassReflection')->willReturn($mockClassReflection);

        $this->scope->method('getFunction')->willReturn($this->createMockFunctionReflection('testFunction'));
        self::assertFalse($this->sut->isInTestMethod());
    }

    private function createMockFunctionReflection(?string $name): mixed
    {
        return (new TestMethodScopeMockFactory())->createDefaultTestMethodScopeMock($name);
    }

    /**
     * @return ClassReflection|MockObject
     */
    private function createMockClassReflectionWithTestSubClass(): ClassReflection|MockObject
    {
        return (new TestMethodScopeMockFactory())->createDefaultTestClassScopeMock();
    }
}
