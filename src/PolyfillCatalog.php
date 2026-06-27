<?php

declare(strict_types=1);

namespace Gared\Polyless;

final class PolyfillCatalog
{
    /**
     * @return array<string, string>
     */
    public function versionPolyfills(): array
    {
        return [
            'symfony/polyfill-php54' => '5.4',
            'symfony/polyfill-php55' => '5.5',
            'symfony/polyfill-php56' => '5.6',
            'symfony/polyfill-php70' => '7.0',
            'symfony/polyfill-php71' => '7.1',
            'symfony/polyfill-php72' => '7.2',
            'symfony/polyfill-php73' => '7.3',
            'symfony/polyfill-php74' => '7.4',
            'symfony/polyfill-php80' => '8.0',
            'symfony/polyfill-php81' => '8.1',
            'symfony/polyfill-php82' => '8.2',
            'symfony/polyfill-php83' => '8.3',
            'symfony/polyfill-php84' => '8.4',
            'symfony/polyfill-php85' => '8.5',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function extensionPolyfills(): array
    {
        return [
            'symfony/polyfill-ctype' => ['ext-ctype'],
            'symfony/polyfill-iconv' => ['ext-iconv'],
            'symfony/polyfill-intl-grapheme' => ['ext-intl'],
            'symfony/polyfill-intl-icu' => ['ext-intl'],
            'symfony/polyfill-intl-idn' => ['ext-intl'],
            'symfony/polyfill-intl-messageformatter' => ['ext-intl'],
            'symfony/polyfill-intl-normalizer' => ['ext-intl'],
            'symfony/polyfill-mbstring' => ['ext-mbstring'],
            'symfony/polyfill-uuid' => ['ext-uuid'],
        ];
    }
}

