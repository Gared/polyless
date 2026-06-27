<?php

declare(strict_types=1);

namespace Gared\Polyless\Tests;

use Gared\Polyless\PhpConstraintEvaluator;
use PHPUnit\Framework\TestCase;

final class PhpConstraintEvaluatorTest extends TestCase
{
    public function testReturnsFalseForMissingConstraint(): void
    {
        $evaluator = new PhpConstraintEvaluator();

        self::assertFalse($evaluator->allowsOnlyVersionsAtLeast(null, '8.0'));
        self::assertFalse($evaluator->allowsOnlyVersionsAtLeast('   ', '8.0'));
    }

    public function testReturnsFalseForInvalidConstraint(): void
    {
        $evaluator = new PhpConstraintEvaluator();

        self::assertFalse($evaluator->allowsOnlyVersionsAtLeast('definitely-not-a-version', '8.0'));
    }

    public function testSubsetChecksAreEvaluatedCorrectly(): void
    {
        $evaluator = new PhpConstraintEvaluator();

        self::assertTrue($evaluator->allowsOnlyVersionsAtLeast('^8.5', '8.4'));
        self::assertFalse($evaluator->allowsOnlyVersionsAtLeast('^8.5', '8.6'));
        self::assertTrue($evaluator->allowsOnlyVersionsAtLeast('>=8.1 <8.3', '8.1'));
        self::assertFalse($evaluator->allowsOnlyVersionsAtLeast('>=8.1 <8.3', '8.2'));
    }
}
