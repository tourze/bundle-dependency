# Symfony Bundle Dependency Interface

A simple interface for defining Symfony bundle dependencies. This package provides a way to manage and resolve bundle dependencies in Symfony applications.

Symfony Bundle 依赖接口，用于定义和管理 Symfony 应用程序中的 Bundle 依赖关系。

## Features 特性

- Simple interface to define bundle dependencies 简单的接口定义 Bundle 依赖
- Automatic dependency resolution 自动依赖解析
- Circular dependency detection 循环依赖检测
- Support for environment-specific dependencies 支持环境特定的依赖

## Installation 安装

```bash
composer require tourze/bundle-dependency
```

## Usage 使用方法

1. Implement the interface in your bundle 在你的 Bundle 中实现接口：

```php
use Tourze\BundleDependency\BundleDependencyInterface;

class YourBundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            'Vendor\DependentBundle' => ['all' => true],
            'Vendor\AnotherBundle' => ['dev' => true, 'test' => true]
        ];
    }
}
```

2. Resolve dependencies 解析依赖：

```php
use Tourze\BundleDependency\ResolveHelper;

// 解析指定 Bundle 的所有依赖
$dependencies = iterator_to_array(ResolveHelper::resolveByBundleName('YourBundle'));

// 或者直接解析一组 Bundle
$bundles = [
    'YourBundle' => ['all' => true]
];
$dependencies = iterator_to_array(ResolveHelper::resolveBundleDependencies($bundles));
```

## License 许可证

MIT
