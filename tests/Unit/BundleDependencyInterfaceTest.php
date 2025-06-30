<?php

declare(strict_types=1);

namespace Tourze\BundleDependency\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tourze\BundleDependency\BundleDependencyInterface;

class BundleDependencyInterfaceTest extends TestCase
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
             * @return array<class-string, array<string, bool>>
             */
            public static function getBundleDependencies(): array
            {
                return [
                    \stdClass::class => ['all' => true],
                ];
            }
        };

        $dependencies = $testBundle::getBundleDependencies();
        $this->assertArrayHasKey(\stdClass::class, $dependencies);
        $this->assertArrayHasKey('all', $dependencies[\stdClass::class]);
        $this->assertTrue($dependencies[\stdClass::class]['all']);
    }
}