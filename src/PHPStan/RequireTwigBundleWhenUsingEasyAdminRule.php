<?php

declare(strict_types=1);

namespace Tourze\BundleDependency\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
class RequireTwigBundleWhenUsingEasyAdminRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();
        if (null === $classReflection) {
            return [];
        }

        // 检查是否实现了 BundleDependencyInterface
        if (!$classReflection->implementsInterface('Tourze\BundleDependency\BundleDependencyInterface')) {
            return [];
        }

        $classNode = $node->getOriginalNode();
        if (!$classNode instanceof Class_) {
            return [];
        }

        // 查找 getBundleDependencies 方法
        $getBundleDependenciesMethod = null;
        foreach ($classNode->getMethods() as $method) {
            if ('getBundleDependencies' === $method->name->toString()) {
                $getBundleDependenciesMethod = $method;
                break;
            }
        }

        if (null === $getBundleDependenciesMethod) {
            return [];
        }

        // 分析方法体中的依赖声明
        $hasEasyAdminBundle = false;
        $hasTwigBundle = false;

        foreach ($getBundleDependenciesMethod->getStmts() ?? [] as $stmt) {
            if ($stmt instanceof Node\Stmt\Return_ && $stmt->expr instanceof Node\Expr\Array_) {
                foreach ($stmt->expr->items as $item) {
                    if (null === $item || null === $item->key) {
                        continue;
                    }

                    if ($item->key instanceof Node\Expr\ClassConstFetch) {
                        $className = $this->getClassNameFromClassConstFetch($item->key);

                        if ('EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle' === $className) {
                            $hasEasyAdminBundle = true;
                        }

                        if ('Symfony\Bundle\TwigBundle\TwigBundle' === $className) {
                            $hasTwigBundle = true;
                        }
                    }
                }
            }
        }

        // 如果声明了 EasyAdminBundle 但没有声明 TwigBundle，报错
        if ($hasEasyAdminBundle && !$hasTwigBundle) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Bundle %s implements BundleDependencyInterface and declares dependency on EasyAdminBundle, but does not declare dependency on TwigBundle. TwigBundle is required when using EasyAdminBundle.',
                    $classReflection->getDisplayName()
                ))
                    ->identifier('bundle.missingTwigBundleDependency')
                    ->build(),
            ];
        }

        return [];
    }

    private function getClassNameFromClassConstFetch(Node\Expr\ClassConstFetch $node): ?string
    {
        if ($node->class instanceof Node\Name) {
            return $node->class->toString();
        }

        return null;
    }
}
