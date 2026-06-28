<?php

declare(strict_types=1);

namespace Gared\Polyless;

final class ExtensionRequirementAdvisor
{
    private PolyfillCatalog $catalog;

    /**
     * @var callable(string): bool
     */
    private $extensionDetector;

    /**
     * @param (callable(string): bool)|null $extensionDetector
     */
    public function __construct(?PolyfillCatalog $catalog = null, ?callable $extensionDetector = null)
    {
        $this->catalog = $catalog ?? new PolyfillCatalog();
        $this->extensionDetector = $extensionDetector ?? static fn (string $extension): bool => extension_loaded($extension);
    }

    /**
     * @param list<string> $installedPackageNames
     * @return array<string, string>
     */
    public function buildSuggestedExtensionRequirements(ProjectContext $context, array $installedPackageNames): array
    {
        $installedPackageNameLookup = [];
        foreach ($installedPackageNames as $packageName) {
            $normalizedPackageName = mb_strtolower($packageName);
            $installedPackageNameLookup[$normalizedPackageName] = $normalizedPackageName;
        }

        $suggestedRequirements = [];

        foreach ($this->catalog->extensionPolyfills() as $packageName => $extensions) {
            $normalizedPackageName = $packageName;
            if (!isset($installedPackageNameLookup[$normalizedPackageName])) {
                continue;
            }

            if ($context->requiresAnyExtension($extensions)) {
                continue;
            }

            foreach ($extensions as $extensionRequirement) {
                if (!$this->isExtensionLoaded($extensionRequirement)) {
                    continue;
                }

                $suggestedRequirements[$normalizedPackageName] = $extensionRequirement;
                break;
            }
        }

        ksort($suggestedRequirements);

        return $suggestedRequirements;
    }

    private function isExtensionLoaded(string $extensionRequirement): bool
    {
        $extensionName = mb_strtolower($extensionRequirement);
        if (str_starts_with($extensionName, 'ext-')) {
            $extensionName = mb_substr($extensionName, 4);
        }

        return ($this->extensionDetector)($extensionName);
    }
}

