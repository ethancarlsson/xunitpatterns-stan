<?php

declare(strict_types=1);

namespace XUnitLint\Tests\Rule\Service;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use XUnitLint\Answerer\NamespaceAnswererImp;
use XUnitLint\Rule\Service\AssertionMethodService;
use PHPUnit\Framework\TestCase;

class AssertionMethodServiceTest extends TestCase
{
    private AssertionMethodService $sut;

    /**
     * @var mixed|Type|MockObject
     */
    private mixed $mockedThrowType;

    protected function setUp(): void
    {
        $this->mockedThrowType = $this->createMock(Type::class);

        $mockedReflectionProvider = $this->createMockedReflectionProvider($this->mockedThrowType);

        $this->sut = new AssertionMethodService($mockedReflectionProvider);

        parent::setUp();
    }

    public function testIsAnAssertionMethod_withNoThrows_returnsFalse(): void
    {
        $answerer = new NamespaceAnswererImp('test');
        $node = new StaticCall(new Name('assertEquals'), 'assertEquals', []);
        $isAnAssertionMethod = $this->sut->isAnAssertionMethod($node, $answerer);
        self::assertFalse(
            $isAnAssertionMethod,
            'Failed asserting a method that does not throw any Exception is not an assertion method'
        );
    }

    public function testIsAnAssertionMethod_withOtherExceptionClass_returnsFalse(): void
    {
        $answerer = new NamespaceAnswererImp('test');
        $node = new StaticCall(new Name('assertEquals'), 'assertEquals', []);

        $this->mockedThrowType
            ->method('getReferencedClasses')
            ->willReturn(['SomeOtherClass']);

        $isAnAssertionMethod = $this->sut->isAnAssertionMethod($node, $answerer);

        self::assertFalse(
            $isAnAssertionMethod,
            'Failed asserting a method that does not throw ExpectationFailedException is not an assertion method'
        );
    }

    public function testIsAnAssertionMethod_withExpectationFailedException_returnsTrue(): void
    {
        $answerer = new NamespaceAnswererImp('test');
        $node = new StaticCall(new Name('assertEquals'), 'assertEquals', []);

        $this->mockedThrowType
            ->method('getReferencedClasses')
            ->willReturn([ExpectationFailedException::class]);

        $isAnAssertionMethod = $this->sut->isAnAssertionMethod($node, $answerer);
        self::assertTrue(
            $isAnAssertionMethod,
            'Failed asserting a method that throws ExpectationFailedException is an assertion method'
        );
    }

    /**
     * PHPParser can't recognise this method because Exception hides ExpectationFailedException
     */
    public function testIsAnAssertionMethod_withPhpUnitException_withNameAssertCount_returnsTrue(): void
    {
        $answerer = new NamespaceAnswererImp('test');
        $node = new StaticCall(new Name('assertCount'), 'assertCount', []);

        $this->mockedThrowType
            ->method('getReferencedClasses')
            ->willReturn([Exception::class]);

        $isAnAssertionMethod = $this->sut->isAnAssertionMethod($node, $answerer);
        self::assertTrue(
            $isAnAssertionMethod,
            'Failed asserting that assertCount is an assertionMethod'
        );
    }

    public function testIsAnAssertionMethod_withNonPHPUnitException_withNameAssertCount_returnsFalse(): void
    {
        $answerer = new NamespaceAnswererImp('test');
        $node = new StaticCall(new Name('assertCount'), 'assertCount', []);

        $this->mockedThrowType
            ->method('getReferencedClasses')
            ->willReturn([]);

        $isAnAssertionMethod = $this->sut->isAnAssertionMethod($node, $answerer);

        self::assertFalse(
            $isAnAssertionMethod,
            'Failed asserting that a method that doesn\'t throw a PHPUnit error is not an assertion method just because of the method name'
        );
    }

    private function createMockedReflectionProvider(Type $throwType): ReflectionProvider
    {
        $mockedProvider = $this->createMock(ReflectionProvider::class);
        $functionReflection = $this->createMock(FunctionReflection::class);
        $functionReflection->method('getThrowType')
            ->willReturn($throwType);

        $mockedProvider->method('getFunction')
            ->willReturn($functionReflection);

        $mockedProvider
            ->method('hasFunction')
            ->willReturn(true);

        return $mockedProvider;
    }
}
