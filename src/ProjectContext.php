<?php

declare(strict_types=1);

namespace Gared\Polyless;

use Composer\Composer;

final class ProjectContext
{
    /**
     * @param list<string> $requiredExtensions
     * @param list<string> $directPolyfillRequirements
     */
    public function __construct(
        private readonly ?string $phpConstraint,
        private readonly array $requiredExtensions,
        private readonly array $directPolyfillRequirements,
    ) {
    }

    public static function fromComposer(Composer $composer): self
    {
        $package = $composer->getPackage();
        $requires = $package->getRequires();

        $phpConstraint = isset($requires['php']) ? $requires['php']->getPrettyConstraint() : null;
        $requiredExtensions = [];
        $directPolyfillRequirements = [];

        foreach ($requires as $packageName => $link) {
            $normalizedName = mb_strtolower($packageName);

            if (str_starts_with($normalizedName, 'ext-')) {
                $requiredExtensions[$normalizedName] = $normalizedName;
                continue;
            }

            if (str_starts_with($normalizedName, 'symfony/polyfill-')) {
                $directPolyfillRequirements[$normalizedName] = $normalizedName;
            }
        }

        $platform = $composer->getConfig()->get('platform');
        if ($phpConstraint === null && isset($platform['php']) && is_string($platform['php']) && $platform['php'] !== '') {
            $phpConstraint = '>=' . $platform['php'];
        }

        foreach ($platform as $packageName => $value) {
            $normalizedName = mb_strtolower((string) $packageName);
            if (!str_starts_with($normalizedName, 'ext-')) {
                continue;
            }

            if ($value === false || $value === '') {
                continue;
            }

            $requiredExtensions[$normalizedName] = $normalizedName;
        }

        return new self(
            $phpConstraint,
            array_values($requiredExtensions),
            array_values($directPolyfillRequirements),
        );
    }

    public function phpConstraint(): ?string
    {
        return $this->phpConstraint;
    }

    /**
     * @param list<string> $extensions
     */
    public function requiresAnyExtension(array $extensions): bool
    {
        foreach ($extensions as $extension) {
            if (in_array(mb_strtolower($extension), $this->requiredExtensions, true)) {
                return true;
            }
        }

        return false;
    }

    public function isDirectlyRequired(string $packageName): bool
    {
        return in_array(mb_strtolower($packageName), $this->directPolyfillRequirements, true);
    }
}


