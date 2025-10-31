<?php

declare(strict_types=1);

namespace Tourze\BundleDependency\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\BundleDependency\ResolutionState;

/**
 * @internal
 */
#[CoversClass(ResolutionState::class)]
final class ResolutionStateTest extends AbstractTestCase
{
    public function testConstructorWithDefaults(): void
    {
        $state = new ResolutionState();

        $this->assertEmpty($state->getResolved());
        $this->assertEmpty($state->getResolving());
    }

    public function testConstructorWithInitialData(): void
    {
        // 创建真实的测试bundle类
        $bundleA = AbstractTestCase::createTestBundle('A');
        $bundleB = AbstractTestCase::createTestBundle('B');

        $resolved = [$bundleA => ['all' => true]];
        $resolving = [$bundleB => true];

        $state = new ResolutionState($resolved, $resolving);

        $this->assertEquals($resolved, $state->getResolved());
        $this->assertEquals($resolving, $state->getResolving());
    }

    public function testIsCircularDependency(): void
    {
        $bundleA = AbstractTestCase::createTestBundle('A');
        $bundleB = AbstractTestCase::createTestBundle('B');

        $resolving = [$bundleA => true];
        $state = new ResolutionState([], $resolving);

        $this->assertTrue($state->isCircularDependency($bundleA));
        $this->assertFalse($state->isCircularDependency($bundleB));
    }

    public function testIsAlreadyResolved(): void
    {
        $bundleA = AbstractTestCase::createTestBundle('A');
        $bundleB = AbstractTestCase::createTestBundle('B');

        $resolved = [$bundleA => ['all' => true]];
        $state = new ResolutionState($resolved);

        $this->assertTrue($state->isAlreadyResolved($bundleA));
        $this->assertFalse($state->isAlreadyResolved($bundleB));
    }

    public function testMarkResolving(): void
    {
        $bundleA = AbstractTestCase::createTestBundle('A');
        $state = new ResolutionState();
        $newState = $state->markResolving($bundleA);

        $this->assertEmpty($state->getResolving());
        $this->assertTrue($newState->isCircularDependency($bundleA));
    }

    public function testUnmarkResolving(): void
    {
        $bundleA = AbstractTestCase::createTestBundle('A');
        $resolving = [$bundleA => true];
        $state = new ResolutionState([], $resolving);
        $newState = $state->unmarkResolving($bundleA);

        $this->assertTrue($state->isCircularDependency($bundleA));
        $this->assertFalse($newState->isCircularDependency($bundleA));
    }

    public function testMarkResolved(): void
    {
        $bundleA = AbstractTestCase::createTestBundle('A');
        $state = new ResolutionState();
        $env = ['all' => true];
        $newState = $state->markResolved($bundleA, $env);

        $this->assertFalse($state->isAlreadyResolved($bundleA));
        $this->assertTrue($newState->isAlreadyResolved($bundleA));
        $this->assertEquals([$bundleA => $env], $newState->getResolved());
    }

    public function testImmutability(): void
    {
        $bundleA = AbstractTestCase::createTestBundle('A');
        $bundleB = AbstractTestCase::createTestBundle('B');
        $bundleC = AbstractTestCase::createTestBundle('C');
        $bundleD = AbstractTestCase::createTestBundle('D');

        $resolved = [$bundleA => ['all' => true]];
        $resolving = [$bundleB => true];
        $state = new ResolutionState($resolved, $resolving);

        $newState1 = $state->markResolving($bundleC);
        $newState2 = $state->unmarkResolving($bundleB);
        $newState3 = $state->markResolved($bundleD, ['all' => true]);

        // 原始状态不应改变
        $this->assertEquals($resolved, $state->getResolved());
        $this->assertEquals($resolving, $state->getResolving());

        // 新状态应该有正确的变化
        $this->assertTrue($newState1->isCircularDependency($bundleC));
        $this->assertFalse($newState2->isCircularDependency($bundleB));
        $this->assertTrue($newState3->isAlreadyResolved($bundleD));
    }
}
