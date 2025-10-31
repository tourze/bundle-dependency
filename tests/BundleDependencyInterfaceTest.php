<?php

declare(strict_types=1);

namespace Tourze\BundleDependency\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

/**
 * @internal
 */
#[CoversClass(BundleDependencyInterface::class)]
final class BundleDependencyInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(BundleDependencyInterface::class));
    }

    public function testInterfaceHasGetBundleDependenciesMethod(): void
    {
        $reflection = new \ReflectionClass(BundleDependencyInterface::class);
        $this->assertTrue($reflection->hasMethod('getBundleDependencies'));

        $method = $reflection->getMethod('getBundleDependencies');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    public function testImplementation(): void
    {
        $testBundle = new class implements BundleDependencyInterface {
            /**
             * @return array<class-string<Bundle>, array<string, bool>>
             */
            public static function getBundleDependencies(): array
            {
                return [
                    Bundle::class => ['all' => true],
                ];
            }
        };

        $dependencies = $testBundle::getBundleDependencies();
        $this->assertArrayHasKey(Bundle::class, $dependencies);
        $this->assertArrayHasKey('all', $dependencies[Bundle::class]);
        $this->assertTrue($dependencies[Bundle::class]['all']);
    }
}
