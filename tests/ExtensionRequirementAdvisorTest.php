<?php

declare(strict_types=1);

namespace Gared\Polyless\Tests;

use Gared\Polyless\ExtensionRequirementAdvisor;
use Gared\Polyless\ProjectContext;
use PHPUnit\Framework\TestCase;

final class ExtensionRequirementAdvisorTest extends TestCase
{
    public function testSuggestsExtensionRequirementWhenPolyfillIsInstalledAndExtensionExists(): void
    {
        $advisor = new ExtensionRequirementAdvisor(
            extensionDetector: static fn (string $extension): bool => $extension === 'mbstring',
        );

        $suggestions = $advisor->buildSuggestedExtensionRequirements(
            new ProjectContext('^8.3', [], []),
            ['symfony/polyfill-mbstring'],
        );

        self::assertSame([
            'symfony/polyfill-mbstring' => 'ext-mbstring',
        ], $suggestions);
    }

    public function testDoesNotSuggestWhenPolyfillIsNotInstalled(): void
    {
        $advisor = new ExtensionRequirementAdvisor(
            extensionDetector: static fn (string $extension): bool => $extension === 'mbstring',
        );

        $suggestions = $advisor->buildSuggestedExtensionRequirements(
            new ProjectContext('^8.3', [], []),
            ['symfony/string'],
        );

        self::assertSame([], $suggestions);
    }

    public function testDoesNotSuggestWhenExtensionAlreadyRequired(): void
    {
        $advisor = new ExtensionRequirementAdvisor(
            extensionDetector: static fn (string $extension): bool => $extension === 'mbstring',
        );

        $suggestions = $advisor->buildSuggestedExtensionRequirements(
            new ProjectContext('^8.3', ['ext-mbstring'], []),
            ['symfony/polyfill-mbstring'],
        );

        self::assertSame([], $suggestions);
    }

    public function testDoesNotSuggestWhenExtensionIsMissing(): void
    {
        $advisor = new ExtensionRequirementAdvisor(
            extensionDetector: static fn (string $extension): bool => false,
        );

        $suggestions = $advisor->buildSuggestedExtensionRequirements(
            new ProjectContext('^8.3', [], []),
            ['symfony/polyfill-mbstring'],
        );

        self::assertSame([], $suggestions);
    }
}

