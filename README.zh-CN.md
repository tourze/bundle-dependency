# Bundle Dependency

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/bundle-dependency.svg?style=flat-square)](https://packagist.org/packages/tourze/bundle-dependency)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/bundle-dependency.svg?style=flat-square)](https://packagist.org/packages/tourze/bundle-dependency)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/bundle-dependency.svg?style=flat-square)](https://packagist.org/packages/tourze/bundle-dependency)
[![License](https://img.shields.io/packagist/l/tourze/bundle-dependency.svg?style=flat-square)](https://packagist.org/packages/tourze/bundle-dependency)

一个轻量级的 Symfony Bundle 依赖管理接口和解析器，支持自动依赖解析和循环依赖检测。

## 功能特性

- **简单接口** - 通过单一方法定义 Bundle 依赖关系
- **自动解析** - 递归解析所有 Bundle 依赖
- **循环检测** - 防止循环依赖（优雅处理）
- **环境支持** - 按环境控制 Bundle 加载（dev、test、prod）
- **轻量级** - 仅需要 PHP 8.1+，无其他外部依赖

## 安装

```bash
composer require tourze/bundle-dependency
```

## 快速开始

### 1. 在你的 Bundle 中实现接口

```php
<?php

use Tourze\BundleDependency\BundleDependencyInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class YourBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            // 在所有环境中加载
            'Vendor\RequiredBundle\RequiredBundle' => ['all' => true],
            
            // 仅在开发和测试环境中加载
            'Vendor\DebugBundle\DebugBundle' => ['dev' => true, 'test' => true],
            
            // 仅在生产环境中加载
            'Vendor\OptimizedBundle\OptimizedBundle' => ['prod' => true],
        ];
    }
}
```

### 2. 解析依赖

```php
<?php

use Tourze\BundleDependency\ResolveHelper;

// 解析一组 Bundle 的所有依赖
$bundles = [
    'App\YourBundle\YourBundle' => ['all' => true],
];

foreach (ResolveHelper::resolveBundleDependencies($bundles) as $bundle => $environments) {
    // $bundle = 'Vendor\RequiredBundle\RequiredBundle'
    // $environments = ['all' => true]
}

// 或者通过 Bundle 名称解析
foreach (ResolveHelper::resolveByBundleName('YourBundle') as $bundleName) {
    // 返回简化的 Bundle 名称
}
```

## 高级用法

### 与 Symfony Kernel 集成

```php
<?php

use Symfony\Component\HttpKernel\Kernel;
use Tourze\BundleDependency\ResolveHelper;

class AppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        $bundles = [
            'App\CoreBundle\CoreBundle' => ['all' => true],
            'App\ApiBundle\ApiBundle' => ['all' => true],
        ];

        // 自动解析并注册所有依赖
        foreach (ResolveHelper::resolveBundleDependencies($bundles) as $bundle => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $bundle();
            }
        }
    }
}
```

### 处理循环依赖

解析器会优雅地处理循环依赖，跳过正在解析的 Bundle：

```php
// BundleA 依赖 BundleB
// BundleB 依赖 BundleA
// 不会抛出异常，两个 Bundle 都只会被解析一次
```

## API 参考

### BundleDependencyInterface

```php
interface BundleDependencyInterface
{
    /**
     * 获取 Bundle 依赖及其环境配置
     *
     * @return array<class-string, array<string, bool>>
     */
    public static function getBundleDependencies(): array;
}
```

### ResolveHelper

```php
class ResolveHelper
{
    /**
     * 递归解析 Bundle 依赖
     *
     * @param array<class-string, array<string, bool>> $bundles 初始 Bundle 列表
     * @return \Traversable<class-string, array<string, bool>> 解析后的 Bundle 列表
     */
    public static function resolveBundleDependencies(array $bundles): \Traversable;

    /**
     * 通过 Bundle 名称解析依赖
     *
     * @param string $bundleName Bundle 名称（如 'YourBundle'）
     * @return \Traversable<string> 简化的 Bundle 名称列表
     */
    public static function resolveByBundleName(string $bundleName): \Traversable;
}
```

## 配置

该包无需配置。只需在你的 Bundle 中实现 `BundleDependencyInterface` 接口，
并使用 `ResolveHelper` 解析依赖即可。

### 环境配置

支持的环境键：
- `'all' => true` - 在所有环境中加载
- `'dev' => true` - 仅在开发环境中加载
- `'test' => true` - 仅在测试环境中加载
- `'prod' => true` - 仅在生产环境中加载

## 贡献指南

1. Fork 本仓库
2. 创建你的功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交你的更改 (`git commit -m 'Add amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 创建一个 Pull Request

## 测试

```bash
# 运行测试
./vendor/bin/phpunit packages/bundle-dependency/tests

# 运行静态分析
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/bundle-dependency
```

## 许可证

MIT 许可证 (MIT)。详情请参阅 [License File](LICENSE)。