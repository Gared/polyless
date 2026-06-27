<?php

declare(strict_types=1);

namespace Gared\Polyless\Tests;

use Gared\Polyless\ProjectContext;
use PHPUnit\Framework\TestCase;

final class ProjectContextTest extends TestCase
{
    public function testExtensionChecksAreCaseInsensitive(): void
    {
        $context = new ProjectContext('^8.3', ['ext-intl', 'ext-mbstring'], []);

        self::assertTrue($context->requiresAnyExtension(['EXT-INTL']));
        self::assertTrue($context->requiresAnyExtension(['ext-uuid', 'EXT-MBSTRING']));
        self::assertFalse($context->requiresAnyExtension(['ext-iconv']));
    }

    public function testDirectRequirementChecksAreCaseInsensitive(): void
    {
        $context = new ProjectContext('^8.3', [], ['symfony/polyfill-php80']);

        self::assertTrue($context->isDirectlyRequired('SYMFONY/POLYFILL-PHP80'));
        self::assertFalse($context->isDirectlyRequired('symfony/polyfill-php81'));
    }

    public function testPhpConstraintAccessorReturnsProvidedValue(): void
    {
        $context = new ProjectContext('>=8.2', [], []);

        self::assertSame('>=8.2', $context->phpConstraint());
    }
}
