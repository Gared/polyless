<?php

declare(strict_types=1);

namespace Gared\Polyless\Tests;

use Composer\Composer;
use Composer\Package\Link;
use Composer\Package\RootPackageInterface;
use Gared\Polyless\ProjectContext;
use PHPUnit\Framework\TestCase;

final class ProjectContextTest extends TestCase
{
    public function testExtensionChecksAreCaseInsensitive(): void
    {
        $context = new ProjectContext('^8.3', ['ext-intl', 'ext-mbstring'], []);

        self::assertTrue($context->requiresAnyExtension(['ext-intl']));
        self::assertTrue($context->requiresAnyExtension(['ext-uuid', 'ext-mbstring']));
        self::assertFalse($context->requiresAnyExtension(['ext-iconv']));
    }

    public function testDirectRequirementChecksAreCaseInsensitive(): void
    {
        $context = new ProjectContext('^8.3', [], ['symfony/polyfill-php80']);

        self::assertTrue($context->isDirectlyRequired('symfony/polyfill-php80'));
        self::assertFalse($context->isDirectlyRequired('symfony/polyfill-php81'));
    }

    public function testPhpConstraintAccessorReturnsProvidedValue(): void
    {
        $context = new ProjectContext('>=8.2', [], []);

        self::assertSame('>=8.2', $context->phpConstraint());
    }

    public function testFromComposerIntlExtensionNotInstalled(): void
    {
        $context = new ProjectContext('>=8.2', [], []);

        $rootPackage = $this->createStub(RootPackageInterface::class);

        $composer = $this->createStub(Composer::class);
        $composer->method('getPackage')->willReturn($rootPackage);
        $rootPackage->method('getRequires')->willReturn([
            'ext-mbstring' => $this->createStub(Link::class),
            'symfony/polyfill-intl-normalizer' => $this->createStub(Link::class),
        ]);

        $newContext = $context->fromComposer($composer);
        self::assertNull($newContext->phpConstraint());
        self::assertFalse($newContext->requiresAnyExtension(['ext-intl']));
        self::assertTrue($newContext->isDirectlyRequired('symfony/polyfill-intl-normalizer'));
    }

    public function testFromComposerIntlExtensionInstalled(): void
    {
        $context = new ProjectContext('>=8.2', [], []);

        $rootPackage = $this->createStub(RootPackageInterface::class);

        $composer = $this->createStub(Composer::class);
        $composer->method('getPackage')->willReturn($rootPackage);
        $rootPackage->method('getRequires')->willReturn([
            'ext-intl' => $this->createStub(Link::class),
            'symfony/polyfill-intl-normalizer' => $this->createStub(Link::class),
        ]);

        $newContext = $context->fromComposer($composer);
        self::assertNull($newContext->phpConstraint());
        self::assertTrue($newContext->requiresAnyExtension(['ext-intl']));
        self::assertTrue($newContext->isDirectlyRequired('symfony/polyfill-intl-normalizer'));
    }
}
