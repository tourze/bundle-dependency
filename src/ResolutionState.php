<?php

declare(strict_types=1);

namespace Tourze\BundleDependency;

/**
 * 内部类：用于管理解析状态，避免使用引用传递
 */
final class ResolutionState
{
    /**
     * @param array<class-string, array<string, bool>> $resolved
     * @param array<class-string, bool>                $resolving
     */
    public function __construct(
        private array $resolved = [],
        private array $resolving = [],
    ) {
    }

    /**
     * @return array<class-string, array<string, bool>>
     */
    public function getResolved(): array
    {
        return $this->resolved;
    }

    /**
     * @return array<class-string, bool>
     */
    public function getResolving(): array
    {
        return $this->resolving;
    }

    public function isCircularDependency(string $bundle): bool
    {
        return isset($this->resolving[$bundle]);
    }

    public function isAlreadyResolved(string $bundle): bool
    {
        return isset($this->resolved[$bundle]);
    }

    /**
     * @param class-string $bundle
     */
    public function markResolving(string $bundle): self
    {
        $newResolving = $this->resolving;
        $newResolving[$bundle] = true;

        return new self($this->resolved, $newResolving);
    }

    /**
     * @param class-string $bundle
     */
    public function unmarkResolving(string $bundle): self
    {
        $newResolving = $this->resolving;
        unset($newResolving[$bundle]);

        return new self($this->resolved, $newResolving);
    }

    /**
     * @param class-string $bundle
     * @param array<string, bool> $env
     */
    public function markResolved(string $bundle, array $env): self
    {
        $newResolved = $this->resolved;
        $newResolved[$bundle] = $env;

        return new self($newResolved, $this->resolving);
    }
}
