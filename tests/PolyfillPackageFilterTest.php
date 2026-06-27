<?php

declare(strict_types=1);

namespace Gared\Polyless\Tests;

use Composer\Package\BasePackage;
use Gared\Polyless\PolyfillPackageFilter;
use Gared\Polyless\ProjectContext;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class PolyfillPackageFilterTest extends TestCase
{
    public function testBuildDisabledPackageNamesUsesPhpAndExtensionSignals(): void
    {
        $filter = new PolyfillPackageFilter();
        $context = new ProjectContext('^8.5', ['ext-intl'], []);

        $disabled = $filter->buildDisabledPackageNames($context);

        self::assertContains('symfony/polyfill-php80', $disabled);
        self::assertContains('symfony/polyfill-php85', $disabled);
        self::assertContains('symfony/polyfill-intl-grapheme', $disabled);
        self::assertNotContains('symfony/polyfill-uuid', $disabled);
    }

    public function testDirectRequirementKeepsExplicitPolyfill(): void
    {
        $filter = new PolyfillPackageFilter();
        $context = new ProjectContext('^8.5', [], ['symfony/polyfill-php80']);

        $disabled = $filter->buildDisabledPackageNames($context);

        self::assertNotContains('symfony/polyfill-php80', $disabled);
        self::assertContains('symfony/polyfill-php81', $disabled);
    }

    public function testFilterRemovesOnlyDisabledCandidatesAndReturnsUniqueNames(): void
    {
        $filter = new PolyfillPackageFilter();
        $context = new ProjectContext('^8.5', ['ext-intl'], []);

        $packages = [
            $this->createPackage('symfony/polyfill-php80'),
            $this->createPackage('symfony/string'),
            $this->createPackage('symfony/polyfill-intl-grapheme'),
            $this->createPackage('symfony/polyfill-php80'),
        ];

        $result = $filter->filter($packages, $context);

        self::assertTrue($result->hasFilteredPackages());
        self::assertSame(['symfony/string'], array_map(
            static fn (BasePackage $package): string => $package->getName(),
            $result->getPackages()
        ));
        self::assertSame(
            ['symfony/polyfill-php80', 'symfony/polyfill-intl-grapheme'],
            $result->getFilteredPackageNames()
        );
    }

    private function createPackage(string $name): BasePackage
    {
        $package = $this->createStub(BasePackage::class);
        $package->method('getName')
            ->willReturn($name);

        return $package;
    }
}
