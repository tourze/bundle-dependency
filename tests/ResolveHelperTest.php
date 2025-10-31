<?php

declare(strict_types=1);

namespace Tourze\BundleDependency\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\BundleDependency\ResolveHelper;

/**
 * @internal
 */
#[CoversClass(ResolveHelper::class)]
final class ResolveHelperTest extends AbstractTestCase
{
    public function testResolveBundleDependencies(): void
    {
        // 创建测试 bundle
        $bundleA = AbstractTestCase::createTestBundle('A');
        /** @var array<class-string, array<string, bool>> $bundleADependencies */
        $bundleADependencies = [$bundleA => ['all' => true]];
        $bundleB = AbstractTestCase::createTestBundle('B', $bundleADependencies);
        /** @var array<class-string, array<string, bool>> $bundleBDependencies */
        $bundleBDependencies = [$bundleB => ['all' => true]];
        $bundleC = AbstractTestCase::createTestBundle('C', $bundleBDependencies);

        // 测试依赖解析
        /** @var array<class-string, array<string, bool>> $bundleCDependencies */
        $bundleCDependencies = [$bundleC => ['all' => true]];
        $dependencies = iterator_to_array(ResolveHelper::resolveBundleDependencies($bundleCDependencies));

        $this->assertCount(3, $dependencies);
        $this->assertArrayHasKey($bundleA, $dependencies);
        $this->assertArrayHasKey($bundleB, $dependencies);
        $this->assertArrayHasKey($bundleC, $dependencies);
    }

    public function testResolveByBundleName(): void
    {
        // 创建测试 bundle
        $bundleA = AbstractTestCase::createTestBundle('A');
        /** @var array<class-string, array<string, bool>> $bundleADeps */
        $bundleADeps = [$bundleA => ['all' => true]];
        $bundleB = AbstractTestCase::createTestBundle('B', $bundleADeps);
        /** @var array<class-string, array<string, bool>> $bundleBDeps */
        $bundleBDeps = [$bundleB => ['all' => true]];
        $bundleC = AbstractTestCase::createTestBundle('C', $bundleBDeps);

        // 测试通过名称解析
        $dependencies = iterator_to_array(ResolveHelper::resolveByBundleName('TestBundle\C'));

        $this->assertCount(3, $dependencies);
        $this->assertContains('A', $dependencies);
        $this->assertContains('B', $dependencies);
        $this->assertContains('C', $dependencies);
    }

    public function testResolveByBundleNameWithInvalidBundle(): void
    {
        $dependencies = iterator_to_array(ResolveHelper::resolveByBundleName('InvalidBundle'));
        $this->assertEmpty($dependencies);
    }
}
