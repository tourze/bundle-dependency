<?php

declare(strict_types=1);

namespace Tourze\BundleDependency;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @see https://github.com/symfony-bundles/bundle-dependency/blob/master/BundleDependencyInterface.php
 */
interface BundleDependencyInterface
{
    /**
     * 获取需要合并的依赖
     *
     * @return array<class-string<Bundle>, array<string, bool>>
     */
    public static function getBundleDependencies(): array;
}
