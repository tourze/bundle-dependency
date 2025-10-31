<?php

declare(strict_types=1);

namespace Tourze\BundleDependency\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\BundleDependency\BundleDependencyInterface;

/**
 * @internal
 */
#[CoversClass(AbstractTestCase::class)]
final class TestCaseTest extends AbstractTestCase
{
    public function testCreateTestBundle(): void
    {
        $bundleName = 'TestBundle' . uniqid();
        /** @var array<class-string, array<string, bool>> $dependencies */
        $dependencies = [
            \stdClass::class => ['all' => true],
        ];

        $className = AbstractTestCase::createTestBundle($bundleName, $dependencies);

        $this->assertInstanceOf(BundleDependencyInterface::class, new $className());
        $this->assertEquals("TestBundle\\{$bundleName}\\{$bundleName}", $className);

        $bundleDependencies = $className::getBundleDependencies();
        $this->assertEquals($dependencies, $bundleDependencies);
    }

    public function testCreateTestBundleWithoutDependencies(): void
    {
        $bundleName = 'EmptyBundle' . uniqid();

        $className = AbstractTestCase::createTestBundle($bundleName);

        $this->assertInstanceOf(BundleDependencyInterface::class, new $className());
        $bundleDependencies = $className::getBundleDependencies();
        $this->assertIsArray($bundleDependencies);
        $this->assertEmpty($bundleDependencies);
    }

    public function testCreateTestBundleMultipleTimes(): void
    {
        $bundleName = 'ReusableBundle' . uniqid();
        /** @var array<class-string, array<string, bool>> $dependencies */
        $dependencies = [\stdClass::class => ['all' => true]];

        // 第一次创建
        $className1 = AbstractTestCase::createTestBundle($bundleName, $dependencies);

        // 第二次创建相同的 bundle
        $className2 = AbstractTestCase::createTestBundle($bundleName, $dependencies);

        // 应该返回相同的类名
        $this->assertEquals($className1, $className2);
        $this->assertInstanceOf(BundleDependencyInterface::class, new $className1());
    }
}
