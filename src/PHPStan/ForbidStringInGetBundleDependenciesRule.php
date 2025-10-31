<?php

namespace Tourze\BundleDependency\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Tourze\BundleDependency\BundleDependencyInterface;

/**
 * @implements Rule<ClassMethod>
 */
class ForbidStringInGetBundleDependenciesRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ('getBundleDependencies' !== $node->name->toString()) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if (!$classReflection || !$classReflection->implementsInterface(BundleDependencyInterface::class)) {
            return [];
        }

        $errors = [];

        if (null === $node->stmts) {
            return [];
        }

        foreach ($node->stmts as $statement) {
            if (!$statement instanceof Return_ || !$statement->expr) {
                continue;
            }

            if (!$statement->expr instanceof Node\Expr\Array_) {
                continue;
            }

            foreach ($statement->expr->items as $item) {
                if ($item instanceof ArrayItem && $item->key instanceof String_) {
                    $errors[] = RuleErrorBuilder::message(
                        'Bundle dependency keys must be specified using ::class constant.'
                    )->line($item->getLine())->build();
                }
            }
        }

        return $errors;
    }
}
