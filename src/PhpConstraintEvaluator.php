<?php

declare(strict_types=1);

namespace Gared\Polyless;

use Composer\Semver\Intervals;
use Composer\Semver\VersionParser;
use UnexpectedValueException;

final class PhpConstraintEvaluator
{
    private VersionParser $versionParser;

    public function __construct(?VersionParser $versionParser = null)
    {
        $this->versionParser = $versionParser ?? new VersionParser();
    }

    public function allowsOnlyVersionsAtLeast(?string $projectConstraint, string $minimumVersion): bool
    {
        if ($projectConstraint === null || trim($projectConstraint) === '') {
            return false;
        }

        try {
            $project = $this->versionParser->parseConstraints($projectConstraint);
            $minimum = $this->versionParser->parseConstraints('>=' . $minimumVersion);
        } catch (UnexpectedValueException) {
            return false;
        }

        return Intervals::isSubsetOf($project, $minimum);
    }
}

