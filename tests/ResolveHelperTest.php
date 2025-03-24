<?php

declare(strict_types=1);

namespace Tourze\BundleDependency\Tests;

use Tourze\BundleDependency\ResolveHelper;

class ResolveHelperTest extends TestCase
{
    public function testResolveBundleDependencies(): void
    {
        // 创建测试 bundle
        $bundleA = self::createTestBundle('A');
        $bundleB = self::createTestBundle('B', [$bundleA => ['all' => true]]);
        $bundleC = self::createTestBundle('C', [$bundleB => ['all' => true]]);

        // 测试依赖解析
        $dependencies = iterator_to_array(ResolveHelper::resolveBundleDependencies([
            $bundleC => ['all' => true]
        ]));

        $this->assertCount(3, $dependencies);
        $this->assertArrayHasKey($bundleA, $dependencies);
        $this->assertArrayHasKey($bundleB, $dependencies);
        $this->assertArrayHasKey($bundleC, $dependencies);
    }

    public function testResolveByBundleName(): void
    {
        // 创建测试 bundle
        $bundleA = self::createTestBundle('A');
        $bundleB = self::createTestBundle('B', [$bundleA => ['all' => true]]);
        $bundleC = self::createTestBundle('C', [$bundleB => ['all' => true]]);

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
