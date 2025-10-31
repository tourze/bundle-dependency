<?php

declare(strict_types=1);

namespace Tourze\BundleDependency;

class ResolveHelper
{
    /**
     * @param array<class-string, array<string, bool>> $bundles
     * @param array<class-string, array<string, bool>> $resolved
     * @param array<class-string, bool>                $resolving
     *
     * @return \Traversable<class-string, array<string, bool>>
     */
    public static function resolveBundleDependencies(array $bundles, array $resolved = [], array $resolving = []): \Traversable
    {
        $state = new ResolutionState($resolved, $resolving);
        yield from self::resolveBundleDependenciesWithState($bundles, $state);
    }

    /**
     * @param array<class-string, array<string, bool>> $bundles
     *
     * @return \Traversable<class-string, array<string, bool>>
     */
    private static function resolveBundleDependenciesWithState(array $bundles, ResolutionState $state): \Traversable
    {
        $currentState = $state;

        foreach ($bundles as $bundle => $env) {
            // 确保 bundle 是字符串类型（防止数字索引数组问题）
            if (!is_string($bundle)) {
                continue;
            }

            if ($currentState->isCircularDependency($bundle)) {
                continue;
            }

            if ($currentState->isAlreadyResolved($bundle)) {
                continue;
            }

            [$resultState, $results] = self::resolveBundleWithState($bundle, $env, $currentState);
            $currentState = $resultState;

            yield from $results;
        }
    }

    /**
     * @param class-string    $bundle
     * @param array<string, bool> $env
     *
     * @return array{ResolutionState, array<class-string, array<string, bool>>}
     */
    private static function resolveBundleWithState(string $bundle, array $env, ResolutionState $state): array
    {
        $workingState = $state->markResolving($bundle);
        $results = [];

        if (is_subclass_of($bundle, BundleDependencyInterface::class)) {
            /** @var array<class-string, array<string, bool>> $dependencies */
            $dependencies = $bundle::getBundleDependencies();
            foreach (self::resolveBundleDependenciesWithState($dependencies, $workingState) as $depBundle => $depEnv) {
                $results[$depBundle] = $depEnv;
                $workingState = $workingState->markResolved($depBundle, $depEnv);
            }
        }

        $workingState = $workingState->unmarkResolving($bundle);
        $workingState = $workingState->markResolved($bundle, $env);
        $results[$bundle] = $env;

        return [$workingState, $results];
    }

    /**
     * @return \Traversable<string>
     */
    public static function resolveByBundleName(string $bundleName): \Traversable
    {
        // 处理类似 "TestBundle\C" 的格式
        $parts = explode('\\', $bundleName);
        $shortName = is_string(end($parts)) ? end($parts) : '';

        $className = "{$bundleName}\\{$shortName}";
        if (!class_exists($className)) {
            return;
        }
        if (!is_subclass_of($className, BundleDependencyInterface::class)) {
            return;
        }

        foreach (self::resolveBundleDependencies([$className => ['all' => true]]) as $bundle => $env) {
            // 满足条件才返回喔
            $tmp = explode('\\', $bundle, 2);
            if (count($tmp) > 1 && $tmp[0] === $tmp[1]) {
                yield $tmp[0];
            } else {
                // 如果格式不匹配，可能是普通的bundle名
                yield basename(str_replace('\\', '/', (string) $bundle));
            }
        }
    }
}
