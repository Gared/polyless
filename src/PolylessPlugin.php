<?php

declare(strict_types=1);

namespace Gared\Polyless;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\Link;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PrePoolCreateEvent;
use Composer\Semver\Constraint\MatchAllConstraint;

final class PolylessPlugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;

    private IOInterface $io;

    private PolyfillPackageFilter $packageFilter;

    private ?ProjectContext $projectContext = null;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->packageFilter = new PolyfillPackageFilter();

        $this->refreshPlan();
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PluginEvents::INIT => 'onInit',
            PluginEvents::PRE_POOL_CREATE => 'onPrePoolCreate',
        ];
    }

    public function onInit(): void
    {
        $this->refreshPlan();
    }

    public function onPrePoolCreate(PrePoolCreateEvent $event): void
    {
        if ($this->projectContext === null) {
            $this->refreshPlan();
        }

        if ($this->projectContext === null) {
            return;
        }

        $result = $this->packageFilter->filter($event->getPackages(), $this->projectContext);
        if (!$result->hasFilteredPackages()) {
            return;
        }

        $event->setPackages($result->getPackages());

        if ($this->io->isVerbose()) {
            $this->io->writeError(sprintf(
                '<info>polyless</info>: filtered %d unnecessary Symfony polyfill package candidates: %s',
                count($result->getFilteredPackageNames()),
                implode(', ', $result->getFilteredPackageNames())
            ));
        }
    }

    private function refreshPlan(): void
    {
        if (!isset($this->composer, $this->io)) {
            return;
        }

        $this->projectContext = ProjectContext::fromComposer($this->composer);
        $disabledPackageNames = $this->packageFilter->buildDisabledPackageNames($this->projectContext);

        if ($disabledPackageNames === []) {
            return;
        }

        $this->injectRootReplacements($disabledPackageNames);

        if ($this->io->isVerbose()) {
            $this->io->write(sprintf(
                '<info>polyless</info>: replacing unnecessary Symfony polyfills for project requirements: %s',
                implode(', ', $disabledPackageNames)
            ));
        }
    }

    /**
     * @param list<string> $packageNames
     */
    private function injectRootReplacements(array $packageNames): void
    {
        $rootPackage = $this->composer->getPackage();
        $replaces = $rootPackage->getReplaces();

        foreach ($packageNames as $packageName) {
            if (isset($replaces[$packageName])) {
                continue;
            }

            $replaces[$packageName] = new Link(
                $rootPackage->getName(),
                $packageName,
                new MatchAllConstraint(),
                Link::TYPE_REPLACE,
                '*'
            );
        }

        $rootPackage->setReplaces($replaces);
    }
}

