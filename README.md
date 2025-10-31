# Bundle Dependency

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/bundle-dependency.svg?style=flat-square)](https://packagist.org/packages/tourze/bundle-dependency)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/bundle-dependency.svg?style=flat-square)](https://packagist.org/packages/tourze/bundle-dependency)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/bundle-dependency.svg?style=flat-square)](https://packagist.org/packages/tourze/bundle-dependency)
[![License](https://img.shields.io/packagist/l/tourze/bundle-dependency.svg?style=flat-square)](https://packagist.org/packages/tourze/bundle-dependency)

A lightweight interface and resolver for managing Symfony bundle dependencies, 
enabling automatic resolution and circular dependency detection.

## Features

- **Simple interface** - Define bundle dependencies with a single method
- **Automatic resolution** - Recursively resolves all bundle dependencies
- **Circular detection** - Prevents circular dependencies (with graceful handling)
- **Environment support** - Control bundle loading per environment (dev, test, prod)
- **Lightweight** - No external dependencies except PHP 8.1+

## Installation

```bash
composer require tourze/bundle-dependency
```

## Quick Start

### 1. Implement the interface in your bundle

```php
<?php

use Tourze\BundleDependency\BundleDependencyInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class YourBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            // Load in all environments
            'Vendor\RequiredBundle\RequiredBundle' => ['all' => true],
            
            // Load only in dev and test environments
            'Vendor\DebugBundle\DebugBundle' => ['dev' => true, 'test' => true],
            
            // Load only in production
            'Vendor\OptimizedBundle\OptimizedBundle' => ['prod' => true],
        ];
    }
}
```

### 2. Resolve dependencies

```php
<?php

use Tourze\BundleDependency\ResolveHelper;

// Resolve all dependencies for a set of bundles
$bundles = [
    'App\YourBundle\YourBundle' => ['all' => true],
];

foreach (ResolveHelper::resolveBundleDependencies($bundles) as $bundle => $environments) {
    // $bundle = 'Vendor\RequiredBundle\RequiredBundle'
    // $environments = ['all' => true]
}

// Or resolve by bundle name
foreach (ResolveHelper::resolveByBundleName('YourBundle') as $bundleName) {
    // Returns simplified bundle names
}
```

## Advanced Usage

### Integration with Symfony Kernel

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

        // Automatically resolve and register all dependencies
        foreach (ResolveHelper::resolveBundleDependencies($bundles) as $bundle => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $bundle();
            }
        }
    }
}
```

### Handling circular dependencies

The resolver gracefully handles circular dependencies by skipping already-resolving bundles:

```php
// BundleA depends on BundleB
// BundleB depends on BundleA
// No exception thrown, both bundles are resolved once
```

## API Reference

### BundleDependencyInterface

```php
interface BundleDependencyInterface
{
    /**
     * Get bundle dependencies with their environment configuration
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
     * Recursively resolve bundle dependencies
     *
     * @param array<class-string, array<string, bool>> $bundles Initial bundles
     * @return \Traversable<class-string, array<string, bool>> Resolved bundles
     */
    public static function resolveBundleDependencies(array $bundles): \Traversable;

    /**
     * Resolve dependencies by bundle name
     *
     * @param string $bundleName Bundle name (e.g., 'YourBundle')
     * @return \Traversable<string> Simplified bundle names
     */
    public static function resolveByBundleName(string $bundleName): \Traversable;
}
```

## Configuration

This package requires no configuration. Simply implement the 
`BundleDependencyInterface` in your bundles and use the `ResolveHelper` 
to resolve dependencies.

### Environment Configuration

Supported environment keys:
- `'all' => true` - Load in all environments
- `'dev' => true` - Load only in development
- `'test' => true` - Load only in testing
- `'prod' => true` - Load only in production

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Testing

```bash
# Run tests
./vendor/bin/phpunit packages/bundle-dependency/tests

# Run static analysis
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/bundle-dependency
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.