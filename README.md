# polyless

`polyless` is a Composer plugin that marks unnecessary `symfony/polyfill-*` packages as replaced by the root project and removes their package candidates from Composer's pool.

## What it does

The current basic implementation disables polyfills when one of these conditions is true:

- the project's PHP constraint guarantees a version that already contains the feature set of a `symfony/polyfill-phpXY` package
- the root project explicitly requires the corresponding extension, for example `ext-mbstring` for `symfony/polyfill-mbstring`
- `config.platform.php` or configured `ext-*` platform entries provide the same guarantee

Direct root requirements for a specific `symfony/polyfill-*` package are left untouched.

## Current examples

If your project requires PHP 8.5:

```json
{
  "require": {
    "php": "^8.5"
  }
}
```

then the plugin can replace packages such as:

- `symfony/polyfill-php80`
- `symfony/polyfill-php81`
- `symfony/polyfill-php82`
- `symfony/polyfill-php83`
- `symfony/polyfill-php84`
- `symfony/polyfill-php85`

If your project requires `ext-intl`, polyfills such as `symfony/polyfill-intl-grapheme` and `symfony/polyfill-intl-normalizer` can also be skipped.
