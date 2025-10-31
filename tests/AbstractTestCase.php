<?php

declare(strict_types=1);

namespace Tourze\BundleDependency\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AbstractTestCase::class)]
abstract class AbstractTestCase extends TestCase
{
    /**
     * @param array<class-string, array<string, bool>> $dependencies
     * @return class-string
     */
    public static function createTestBundle(string $name, array $dependencies = []): string
    {
        $className = "TestBundle\\{$name}\\{$name}";
        if (!class_exists($className)) {
            eval("
                namespace TestBundle\\{$name};
                class {$name} implements \\Tourze\\BundleDependency\\BundleDependencyInterface {
                    public static function getBundleDependencies(): array {
                        return " . var_export($dependencies, true) . ';
                    }
                }
            ');
        }

        /** @var class-string $className */
        return $className;
    }
}
