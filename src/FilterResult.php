<?php

declare(strict_types=1);

namespace Gared\Polyless;

use Composer\Package\BasePackage;

final class FilterResult
{
    /**
     * @param array<BasePackage> $packages
     * @param list<string> $filteredPackageNames
     */
    public function __construct(
        private readonly array $packages,
        private readonly array $filteredPackageNames,
    ) {
    }

    /**
     * @return array<BasePackage>
     */
    public function getPackages(): array
    {
        return $this->packages;
    }

    /**
     * @return list<string>
     */
    public function getFilteredPackageNames(): array
    {
        return $this->filteredPackageNames;
    }

    public function hasFilteredPackages(): bool
    {
        return $this->filteredPackageNames !== [];
    }
}

