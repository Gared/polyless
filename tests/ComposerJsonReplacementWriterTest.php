<?php

declare(strict_types=1);

namespace Gared\Polyless\Tests;

use Gared\Polyless\ComposerJsonReplacementWriter;
use PHPUnit\Framework\TestCase;

final class ComposerJsonReplacementWriterTest extends TestCase
{
    public function testPersistsCalculatedReplacementsIntoComposerJson(): void
    {
        $workdir = $this->createTempDirectory();
        $composerJsonPath = $workdir . DIRECTORY_SEPARATOR . 'composer.json';
        file_put_contents($composerJsonPath, <<<'JSON'
{
    "name": "ci/test",
    "require": {
        "php": ">=8.5"
    }
}
JSON);

        $writer = new ComposerJsonReplacementWriter($composerJsonPath);
        self::assertTrue($writer->persist([
            'symfony/polyfill-php84',
            'symfony/polyfill-mbstring',
        ]));

        $decoded = json_decode((string) file_get_contents($composerJsonPath), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        self::assertIsArray($decoded['replace']);
        self::assertIsArray($decoded['require']);
        self::assertSame('*', $decoded['replace']['symfony/polyfill-mbstring']);
        self::assertSame('*', $decoded['replace']['symfony/polyfill-php84']);
        self::assertSame('>=8.5', $decoded['require']['php']);
    }

    public function testReturnsFalseWhenComposerJsonIsMissing(): void
    {
        $writer = new ComposerJsonReplacementWriter($this->createTempDirectory() . DIRECTORY_SEPARATOR . 'composer.json');

        self::assertFalse($writer->persist(['symfony/polyfill-php84']));
    }

    private function createTempDirectory(): string
    {
        $baseDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'polyless-' . bin2hex(random_bytes(8));
        mkdir($baseDir);

        return $baseDir;
    }
}

