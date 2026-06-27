# polyless

`polyless` is a Composer plugin that marks unnecessary `symfony/polyfill-*` packages as replaced by the root project and removes their package candidates from Composer's pool.

## What it does

The current basic implementation disables polyfills when one of these conditions is true:

- the project's PHP constraint guarantees a version that already contains the feature set of a `symfony/polyfill-phpXY` package
- the root project explicitly requires the corresponding extension, for example `ext-mbstring` for `symfony/polyfill-mbstring`
- `config.platform.php` or configured `ext-*` platform entries provide the same guarantee

Direct root requirements for a specific `symfony/polyfill-*` package are left untouched.

## Usage

Add this package to your project dev dependencies:

```bash
composer require --dev gared/polyless
```

You need to allow this package to run as a plugin!

After that run:

```bash
composer update symfony/polyfill-*
```

to remove any unnecessary polyfill packages from your project.

## Current examples

### PHP polyfills

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

### Extension polyfills

If you composer.lock contains `symfony/polyfill-mbstring` you can run the following command to require the `ext-mbstring` extension instead (of course you need to have the PHP extension installed):

```bash
composer require ext-mbstring
```

this will automatically remove the `symfony/polyfill-mbstring` package from your project.
