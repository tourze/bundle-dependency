<?php

declare(strict_types=1);

namespace Tourze\BundleDependency\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected static function createTestBundle(string $name, array $dependencies = []): string
    {
        $className = "TestBundle\\{$name}\\{$name}";
        if (!class_exists($className)) {
            eval("
                namespace TestBundle\\{$name};
                class {$name} implements \\Tourze\\BundleDependency\\BundleDependencyInterface {
                    public static function getBundleDependencies(): array {
                        return " . var_export($dependencies, true) . ";
                    }
                }
            ");
        }
        return $className;
    }
}
