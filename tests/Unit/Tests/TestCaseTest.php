<?php

declare(strict_types=1);

namespace Tourze\BundleDependency\Tests\Unit\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Tourze\BundleDependency\Tests\TestCase;

class TestCaseTest extends PHPUnitTestCase
{
    public function testCreateTestBundle(): void
    {
        $bundleName = 'TestBundle' . uniqid();
        /** @var array<class-string, array<string, bool>> $dependencies */
        $dependencies = [
            \stdClass::class => ['all' => true],
        ];

        $className = TestCase::createTestBundle($bundleName, $dependencies);
        
        $this->assertTrue(class_exists($className));
        $this->assertEquals("TestBundle\\{$bundleName}\\{$bundleName}", $className);
        
        $bundleDependencies = $className::getBundleDependencies();
        $this->assertEquals($dependencies, $bundleDependencies);
    }

    public function testCreateTestBundleWithoutDependencies(): void
    {
        $bundleName = 'EmptyBundle' . uniqid();
        
        $className = TestCase::createTestBundle($bundleName);
        
        $this->assertTrue(class_exists($className));
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
        $className1 = TestCase::createTestBundle($bundleName, $dependencies);
        
        // 第二次创建相同的 bundle
        $className2 = TestCase::createTestBundle($bundleName, $dependencies);
        
        // 应该返回相同的类名
        $this->assertEquals($className1, $className2);
        $this->assertTrue(class_exists($className1));
    }
}