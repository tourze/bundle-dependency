<?php

declare(strict_types=1);

namespace Tourze\BundleDependency;

/**
 * @see https://github.com/symfony-bundles/bundle-dependency/blob/master/BundleDependencyInterface.php
 */
interface BundleDependencyInterface
{
    /**
     * 获取需要合并的依赖
     *
     * @return array<class-string, array<string, bool>>
     */
    public static function getBundleDependencies(): array;
}
