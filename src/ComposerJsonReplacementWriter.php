<?php

declare(strict_types=1);

namespace Gared\Polyless;

use Composer\Json\JsonManipulator;

final readonly class ComposerJsonReplacementWriter
{
    public function __construct(private ?string $composerJsonPath = null)
    {
    }

    /**
     * @param list<lowercase-string> $packageNames
     */
    public function persist(array $packageNames): bool
    {
        $composerJsonPath = $this->composerJsonPath ?? getcwd() . DIRECTORY_SEPARATOR . 'composer.json';
        if (!is_file($composerJsonPath) || !is_readable($composerJsonPath)) {
            return false;
        }

        $contents = file_get_contents($composerJsonPath);
        if ($contents === false) {
            return false;
        }

        $manipulator = new JsonManipulator($contents);
        $changed = false;

        foreach (array_values(array_unique($packageNames)) as $packageName) {
            $changed = $manipulator->addLink('replace', $packageName, '*', true) || $changed;
        }

        if (!$changed) {
            return false;
        }

        file_put_contents($composerJsonPath, $manipulator->getContents());

        return true;
    }
}

