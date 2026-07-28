# npm-only Toolchain Design

## Goal

Convert Fancyn Plugin Starter into a Composer-free starter whose development, testing, initialization, build, and packaging workflows are driven only through npm. PHP 8.1 or newer remains required to run WordPress, PHPUnit, and PHPCS.

## User workflow

A new project starts with:

```bash
git clone <repository-url>
cd fancyn-plugin-starter
npm run init
npm install
npm run package
```

The supported commands are:

```bash
npm run init
npm run dev
npm run lint
npm test
npm run build
npm run validate:build
npm run package
```

No command may require a globally installed Composer executable. The production ZIP must require neither Node.js, npm, nor Composer after installation in WordPress.

## Composer removal

Remove `composer.json`, `composer.lock`, Composer scripts, and every reference to `vendor/autoload.php`. The release package must not contain `vendor/` or Composer manifests.

The plugin will load its own classes through a focused PSR-4-style autoloader. It maps the configured project namespace to `includes/`, converts namespace separators to directory separators, accepts only classes inside that namespace, and includes a class file only when its resolved path remains inside `includes/`.

The plugin bootstrap loads this autoloader directly before constructing services. The rename workflow updates the namespace recognized by the autoloader.

## PHP tool provisioning

`npm install` runs a cross-platform Node script at `scripts/setup-php-tools.mjs`. The script provisions version-pinned PHP development tools into the ignored `tools/` directory:

- PHPUnit PHAR.
- PHPCS.
- WordPress Coding Standards.
- PHPCSUtils.
- PHPCSExtra.
- PHPCompatibility, PHPCompatibilityParagonie, and PHPCompatibilityWP.

Tool metadata lives in a tracked JSON manifest containing the version, canonical HTTPS download URL, and SHA-256 digest for every archive or PHAR. Setup downloads to a temporary file, verifies its digest before extraction or installation, and performs an atomic move into its final versioned location. A matching cached tool is reused. A checksum mismatch, incomplete extraction, missing PHP executable, or unsupported PHP version stops setup with an actionable error and leaves the previous valid tool installation intact.

The setup script writes the local PHPCS `installed_paths` configuration needed by WPCS. npm scripts invoke tools through explicit local paths and the current PHP executable. They never depend on global PHPUnit, PHPCS, WPCS, or Composer installations.

`tools/` is excluded from Git and release ZIPs.

## PHP test infrastructure

PHPUnit PHAR does not include Brain Monkey or Mockery. Remove those dependencies and replace them with a repository-owned WordPress test shim.

The shim provides only the WordPress functions used by this starter. It supports:

- Setting deterministic return values.
- Registering callback implementations.
- Recording call count and arguments.
- Simulating JSON and termination behavior used by REST and AJAX handlers.
- Resetting all state between tests.

Tests assert observable plugin behavior and recorded WordPress calls. The shim must not add test-only methods to production classes. Existing PHP coverage for bootstrap, lifecycle, admin assets, settings, components, rendering, REST, and AJAX must remain equivalent after Brain Monkey and Mockery are removed.

## Interactive project initialization

`npm run init` runs with Node built-ins and therefore works before `npm install`. It prompts for:

- Plugin name.
- Plugin slug.
- PHP namespace.
- PHP prefix.
- CSS prefix.

It derives:

- Text domain from the plugin slug.
- Option prefix from the PHP prefix.
- REST namespace as `<plugin-slug>/v1`.
- Main plugin filename as `<plugin-slug>.php`.

Before changing files, the command displays the complete resolved configuration and asks for confirmation.

Validation rules are:

- Plugin slug contains lowercase ASCII letters, digits, and hyphens only.
- PHP namespace contains at least two valid PHP identifier segments.
- PHP and option prefixes use lowercase snake case and end in `_`.
- CSS prefix contains lowercase ASCII letters, digits, and hyphens and ends in `-`.
- Text domain equals the plugin slug.
- REST namespace equals `<plugin-slug>/v1`.

Initialization reuses the existing atomic rename engine. It stages every file and filename transformation in a temporary directory, validates the staged result, verifies that starter placeholders are gone, and then swaps the result into the project. Cancellation or failure leaves the original project unchanged. Running init again with identical metadata is safe; attempting to apply different metadata to an initialized project is rejected.

The non-interactive JSON rename interface may remain as an internal/testable engine, but the documented primary user interface is interactive `npm run init`.

## Package flow

`npm run package` performs, in order:

1. JavaScript and CSS linting.
2. PHPCS with WordPress Coding Standards.
3. Vitest and PHPUnit.
4. Vite production build.
5. Build and CSS isolation validation.
6. ZIP staging and archive creation.

The archive contains the plugin bootstrap, internal PHP autoloader, `includes/`, templates, compiled assets, languages, and README. It excludes development sources and configuration, tests, scripts, `node_modules/`, `tools/`, `vendor/`, Composer manifests, repository metadata, and source maps.

The final package is installed and activated on WordPress without running a dependency installation command on the server.

## Tests and acceptance criteria

Automated tests must prove:

- The internal autoloader resolves valid project classes and rejects paths outside `includes/`.
- Plugin bootstrap contains no Composer or `vendor/autoload.php` dependency.
- PHP tool setup verifies checksums, reuses a valid cache, rejects corrupt downloads, and rolls back failed setup.
- All npm commands work when Composer is unavailable on `PATH`.
- The WordPress test shim resets state and correctly records calls, return values, callbacks, JSON responses, and termination.
- Existing PHP behavior tests retain equivalent coverage without Brain Monkey or Mockery.
- Interactive init validates every input, supports cancellation, applies derived defaults, is atomic, is idempotent for identical metadata, and rejects different metadata after initialization.
- Placeholder scans include Composer-related remnants.
- The generated ZIP contains the internal autoloader and excludes `vendor/`, `tools/`, `composer.json`, and `composer.lock`.

Final acceptance runs from a clean clone on Windows and one Unix-like environment:

```bash
npm run init
npm install
npm run lint
npm test
npm run package
```

The renamed ZIP must install and activate on WordPress 6.5 or newer with PHP 8.1 or newer, render plugin pages, save settings, load integrations, preserve CSS isolation, and pass the existing responsive and keyboard checks.

## Documentation changes

README and acceptance documentation will:

- Remove Composer installation and troubleshooting steps.
- Make `npm run init` the first project-customization command.
- Explain the required PHP and Node/npm versions.
- Explain local PHP tool caching and how to refresh it.
- Document checksum failures and offline behavior.
- Show the npm-only lint, test, build, and package workflow.
- State explicitly that the release ZIP has no runtime dependency manager.

## Out of scope

- Reimplementing PHPUnit or PHPCS in JavaScript.
- Shipping PHP development tools in the production ZIP.
- Supporting PHP versions older than 8.1.
- Providing a graphical setup wizard.
- Publishing PHP tools to a custom package registry.
- Keeping Composer as an optional fallback path.
