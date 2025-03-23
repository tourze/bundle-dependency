<?php

declare(strict_types=1);

namespace Tourze\BundleDependency;

class ResolveHelper
{
    public static function resolveBundleDependencies(array $bundles, array &$resolved = [], array $resolving = []): \Traversable
    {
        foreach ($bundles as $bundle => $env) {
            if (isset($resolving[$bundle])) {
                continue;
                //throw new RuntimeException("循环依赖检测到：{$bundle}");
            }
            if (!isset($resolved[$bundle])) {
                $resolving[$bundle] = true;
                if (is_subclass_of($bundle, BundleDependencyInterface::class)) {
                    $dependencies = $bundle::getBundleDependencies();
                    foreach (static::resolveBundleDependencies($dependencies, $resolved, $resolving) as $_bundle => $_env) {
                        yield $_bundle => $_env;
                    }
                }
                unset($resolving[$bundle]);
                $resolved[$bundle] = $env;
                yield $bundle => $env;
            }
        }
        //return $resolved;
    }

    public static function resolveByBundleName(string $bundleName): \Traversable
    {
        $className = "{$bundleName}\\{$bundleName}";
        if (!class_exists($className)) {
            return;
        }
        if (!is_subclass_of($className, BundleDependencyInterface::class)) {
            return;
        }

        foreach (static::resolveBundleDependencies([$className => ['all' => true]]) as $bundle => $env) {
            // 满足条件才返回喔
            $tmp = explode('\\', $bundle, 2);
            if ($tmp[0] === $tmp[1]) {
                yield $tmp[0];
            }
        }
    }
}
