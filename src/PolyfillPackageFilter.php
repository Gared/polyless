<?php

declare(strict_types=1);

namespace Gared\Polyless;

use Composer\Package\BasePackage;

final class PolyfillPackageFilter
{
    private PolyfillCatalog $catalog;

    private PhpConstraintEvaluator $phpConstraintEvaluator;

    public function __construct(
        ?PolyfillCatalog $catalog = null,
        ?PhpConstraintEvaluator $phpConstraintEvaluator = null,
    ) {
        $this->catalog = $catalog ?? new PolyfillCatalog();
        $this->phpConstraintEvaluator = $phpConstraintEvaluator ?? new PhpConstraintEvaluator();
    }

    /**
     * @return list<lowercase-string>
     */
    public function buildDisabledPackageNames(ProjectContext $context): array
    {
        $disabledPackageNames = [];

        foreach ($this->catalog->versionPolyfills() as $packageName => $minimumPhpVersion) {
            if ($context->isDirectlyRequired($packageName)) {
                continue;
            }

            if (!$this->phpConstraintEvaluator->allowsOnlyVersionsAtLeast($context->phpConstraint(), $minimumPhpVersion)) {
                continue;
            }

            $disabledPackageNames[$packageName] = $packageName;
        }

        foreach ($this->catalog->extensionPolyfills() as $packageName => $extensions) {
            if ($context->isDirectlyRequired($packageName)) {
                continue;
            }

            if (!$context->requiresAnyExtension($extensions)) {
                continue;
            }

            $disabledPackageNames[$packageName] = $packageName;
        }

        ksort($disabledPackageNames);

        return array_values($disabledPackageNames);
    }

    /**
     * @param array<BasePackage> $packages
     */
    public function filter(array $packages, ProjectContext $context): FilterResult
    {
        $disabledPackageNames = array_flip($this->buildDisabledPackageNames($context));
        if ($disabledPackageNames === []) {
            return new FilterResult($packages, []);
        }

        $filteredPackages = [];
        $filteredPackageNames = [];

        foreach ($packages as $package) {
            $packageName = mb_strtolower($package->getName());
            if (!isset($disabledPackageNames[$packageName])) {
                $filteredPackages[] = $package;
                continue;
            }

            $filteredPackageNames[$packageName] = $packageName;
        }

        return new FilterResult($filteredPackages, array_values($filteredPackageNames));
    }
}

