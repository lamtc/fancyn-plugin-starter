# npm-only WordPress Plugin Starter Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove Composer completely while preserving WordPress Coding Standards, PHPUnit coverage, atomic project renaming, production packaging, and the existing WordPress runtime behavior through npm-only commands.

**Architecture:** A small project-owned PHP autoloader replaces Composer runtime autoloading. A checksum-pinned Node provisioner installs PHPUnit, PHPCS, WPCS, and their standards under ignored `tools/`; a repository-owned WordPress function shim replaces Brain Monkey and Mockery. The existing rename engine becomes reusable by an interactive, dependency-free `npm run init` command, while packaging explicitly rejects Composer and development-tool remnants.

**Tech Stack:** Node.js 20.19+, npm 10+, PHP 8.1+, PHPUnit PHAR, PHP_CodeSniffer, WordPress Coding Standards, Vitest, Vite 8, Tailwind CSS 3, WordPress 6.5+.

---

## Fixed decisions

- Users run only npm commands; Composer is neither required nor supported.
- PHP remains a system requirement and is invoked by npm scripts.
- `npm run init` works before `npm install` and uses Node built-ins only.
- `npm install` provisions pinned PHP tools through `postinstall`.
- Downloaded PHP tools live under `tools/`, are ignored by Git, and never enter ZIP files.
- Production uses `includes/Autoloader.php`; no generated runtime autoloader exists.
- Brain Monkey and Mockery are removed rather than recreated or downloaded transitively.
- Existing behavior coverage and live WordPress acceptance remain mandatory.
- The JSON rename mode remains supported for automation; interactive init is the primary documented workflow.

## Target file map

```text
fancyn-plugin-starter/
├── includes/Autoloader.php                 # Project namespace to includes/ loader
├── scripts/
│   ├── init-plugin.mjs                     # Interactive prompts and confirmation
│   ├── rename-plugin.mjs                   # Reusable atomic rename engine and JSON CLI
│   ├── setup-php-tools.mjs                 # Download/cache/checksum/extract orchestration
│   ├── run-php-tool.mjs                    # Cross-platform PHP tool runner
│   └── build-zip.mjs                       # Composer-free package staging
├── tools/
│   └── php-tools.json                      # Tracked version/URL/SHA-256 manifest
├── tests/
│   ├── php/
│   │   ├── bootstrap.php                   # PHPUnit PHAR bootstrap
│   │   ├── AutoloaderTest.php
│   │   └── support/
│   │       ├── WordPress.php               # Global WordPress function shims
│   │       ├── WordPressTestCase.php        # Reset/assertion base class
│   │       └── WordPressShimTest.php
│   ├── scripts/
│   │   ├── init-plugin.test.js
│   │   ├── setup-php-tools.test.js
│   │   ├── npm-only.test.js
│   │   └── build-zip.test.js
│   └── acceptance/package-smoke.md
├── package.json
├── package-lock.json
├── phpunit.xml.dist
├── phpcs.xml.dist
├── .gitignore
├── .distignore
└── README.md
```

Files removed by the completed migration:

```text
composer.json
composer.lock
vendor/
```

---

## Task 1: Add a checksum-pinned PHP tool provisioner

**Files:**
- Create: `tools/php-tools.json`
- Create: `scripts/setup-php-tools.mjs`
- Create: `tests/scripts/setup-php-tools.test.js`
- Modify: `.gitignore`
- Modify: `.distignore`
- Modify: `package.json`

- [ ] **Step 1: Write failing provisioner tests**

Create fixture-driven tests that never use the network. Inject `fetch`, hashing, extraction, filesystem root, and PHP-version probing into `setupPhpTools()`:

```js
import { createHash } from 'node:crypto';
import { mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { afterEach, describe, expect, it, vi } from 'vitest';

import { setupPhpTools } from '../../scripts/setup-php-tools.mjs';

const roots = [];
const sha256 = (value) => createHash('sha256').update(value).digest('hex');

describe('setupPhpTools', () => {
  afterEach(async () => Promise.all(roots.splice(0).map((root) => rm(root, { recursive: true, force: true }))));

  it('verifies downloads and atomically installs a pinned tool', async () => {
    const root = await mkdtemp(path.join(tmpdir(), 'fps-tools-'));
    roots.push(root);
    const body = Buffer.from('phpunit fixture');
    const manifest = { tools: [{ name: 'phpunit', version: '10.5.64', type: 'phar', url: 'https://example.test/phpunit.phar', sha256: sha256(body) }] };
    await setupPhpTools({ root, manifest, fetch: vi.fn(async () => new Response(body)), phpVersion: async () => '8.3.25' });
    expect(await readFile(path.join(root, 'tools', 'phpunit', '10.5.64', 'phpunit.phar'), 'utf8')).toBe('phpunit fixture');
  });

  it('rejects a checksum mismatch without replacing a valid cache', async () => {
    const root = await mkdtemp(path.join(tmpdir(), 'fps-tools-'));
    roots.push(root);
    const destination = path.join(root, 'tools', 'phpunit', '10.5.64');
    const manifest = { tools: [{ name: 'phpunit', version: '10.5.64', type: 'phar', url: 'https://example.test/phpunit.phar', sha256: sha256('expected') }] };
    await expect(setupPhpTools({ root, manifest, fetch: vi.fn(async () => new Response('corrupt')), phpVersion: async () => '8.3.25' })).rejects.toThrow(/checksum/i);
    await expect(readFile(path.join(destination, 'phpunit.phar'))).rejects.toThrow();
  });
});
```

- [ ] **Step 2: Run the tests and verify RED**

Run:

```powershell
npx vitest run tests/scripts/setup-php-tools.test.js
```

Expected: FAIL because `scripts/setup-php-tools.mjs` does not exist.

- [ ] **Step 3: Implement the provisioner boundary**

Export these stable interfaces:

```js
export async function setupPhpTools({
  root = process.cwd(),
  manifest,
  fetch = globalThis.fetch,
  phpVersion = detectPhpVersion,
  extract = extractArchive,
} = {}) {}

export async function verifyChecksum(buffer, expectedSha256) {}
export async function detectPhpVersion() {}
```

Implementation rules:

- Reject PHP older than 8.1 before downloading.
- Validate HTTPS URLs, 64-character lowercase SHA-256 values, unique tool names, and nonempty versions.
- Download to `tools/.staging/<name>-<random>`.
- Hash bytes before writing the final artifact.
- Extract archives through `adm-zip`; copy PHARs directly.
- Write `.complete.json` containing name, version, and checksum.
- Reuse a cache only when `.complete.json` matches the manifest.
- Rename staging to the versioned destination only after all validation passes.
- Always remove the current staging directory in `finally`; never delete an existing valid destination on failure.

- [ ] **Step 4: Pin official releases**

Populate `tools/php-tools.json` with exact official HTTPS release artifacts and SHA-256 digests for:

```json
{
  "schemaVersion": 1,
  "php": ">=8.1",
  "tools": [
    { "name": "phpunit", "version": "10.5.64", "type": "phar", "target": "phpunit.phar", "url": "https://phar.phpunit.de/phpunit-10.5.64.phar", "sha256": "a823d916151f628dd9943ccc81a98bcfbba9c5babf53f27be6c7dccc89f8ee23" },
    { "name": "phpcs", "version": "3.13.5", "type": "archive", "target": "phpcs", "url": "https://github.com/PHPCSStandards/PHP_CodeSniffer/archive/refs/tags/3.13.5.zip", "sha256": "7684dbac53b9acb362829cb4db5d6da94ccc20fc65c6571a2281cdbc50721880" },
    { "name": "wpcs", "version": "3.4.1", "type": "archive", "target": "wpcs", "url": "https://github.com/WordPress/WordPress-Coding-Standards/archive/refs/tags/3.4.1.zip", "sha256": "18314ef61be2021597ac7526482586a5791407fb73555c26022708efedd2e867" },
    { "name": "phpcsutils", "version": "1.2.3", "type": "archive", "target": "phpcsutils", "url": "https://github.com/PHPCSStandards/PHPCSUtils/archive/refs/tags/1.2.3.zip", "sha256": "b6fbddc95dcd0a69f8a29d54f0b4215ec545afa47434700a9ccccf8e629256c6" },
    { "name": "phpcsextra", "version": "1.5.1", "type": "archive", "target": "phpcsextra", "url": "https://github.com/PHPCSStandards/PHPCSExtra/archive/refs/tags/1.5.1.zip", "sha256": "9ef938ab26605e5fb2a84bfc6a7caf9cb03713625ccdded6305739a02d89f6b2" },
    { "name": "phpcompatibility", "version": "9.3.5", "type": "archive", "target": "phpcompatibility", "url": "https://github.com/PHPCompatibility/PHPCompatibility/archive/refs/tags/9.3.5.zip", "sha256": "e82b45aa4b80e118298da59328bcb51135fdbf4d9d6668bc9a672c810605c4b5" },
    { "name": "phpcompatibility-paragonie", "version": "1.3.4", "type": "archive", "target": "phpcompatibility-paragonie", "url": "https://github.com/PHPCompatibility/PHPCompatibilityParagonie/archive/refs/tags/1.3.4.zip", "sha256": "90497071f4886614d0c2cf017e78f84a0e5ed4492a88204fe6ec0466b4211df1" },
    { "name": "phpcompatibility-wp", "version": "2.1.8", "type": "archive", "target": "phpcompatibility-wp", "url": "https://github.com/PHPCompatibility/PHPCompatibilityWP/archive/refs/tags/2.1.8.zip", "sha256": "247707753baa7507c29be7b2b4116e0c3a52acdb2d6e454fa594bdbe2220412b" }
  ]
}
```

Add a manifest test that rejects any non-hex or missing digest. Do not accept `latest` URLs or calculate trusted values at installation time.

- [ ] **Step 5: Wire npm postinstall and exclusions**

Add:

```json
{
  "scripts": {
    "postinstall": "node scripts/setup-php-tools.mjs"
  }
}
```

Add `/tools/*/`, `/tools/.staging/`, and `/tools/.phpcs-cache` to `.gitignore`, while negating `!/tools/php-tools.json`. Add `tools/` to `.distignore`.

- [ ] **Step 6: Verify GREEN and commit**

Run:

```powershell
npx vitest run tests/scripts/setup-php-tools.test.js
npm install
```

Expected: tests pass; setup reports pinned tool versions; a second `npm install` reports cache reuse.

Commit:

```powershell
git add tools/php-tools.json scripts/setup-php-tools.mjs tests/scripts/setup-php-tools.test.js package.json package-lock.json .gitignore .distignore
git commit -m "build: provision pinned PHP tools through npm"
```

## Task 2: Run PHPCS and PHPUnit only through local npm tools

**Files:**
- Create: `scripts/run-php-tool.mjs`
- Create: `tests/scripts/run-php-tool.test.js`
- Modify: `package.json`
- Modify: `phpcs.xml.dist`

- [ ] **Step 1: Write failing runner tests**

Test that `runPhpTool('phpunit', args)` resolves the manifest version, invokes `php` with the local PHAR, forwards exit codes, and never searches global Composer binaries.

```js
it('runs the locally provisioned PHPUnit PHAR through PHP', async () => {
  const spawn = vi.fn(() => ({ status: 0 }));
  const result = await runPhpTool('phpunit', ['--version'], { root: fixtureRoot, spawn });
  expect(spawn).toHaveBeenCalledWith('php', [expect.stringMatching(/tools[\\/]phpunit[\\/]10\.5\.64[\\/]phpunit\.phar$/), '--version'], expect.objectContaining({ stdio: 'inherit' }));
  expect(result).toBe(0);
});
```

- [ ] **Step 2: Verify RED**

Run `npx vitest run tests/scripts/run-php-tool.test.js`.

Expected: FAIL because the runner module is missing.

- [ ] **Step 3: Implement the runner and PHPCS installed paths**

Support `phpunit`, `phpcs`, and `phpcbf`. For PHPCS, invoke its local `bin/phpcs` or `bin/phpcbf` entry and set `PHP_CODESNIFFER_VERBOSITY=0`. During setup, write `tools/phpcs-installed-paths.json` and pass the standards directories with PHPCS `--runtime-set installed_paths <comma-separated-paths>` on every run; do not mutate global PHPCS configuration.

- [ ] **Step 4: Replace npm scripts**

Use only Node/PHP commands:

```json
{
  "scripts": {
    "lint:php": "node scripts/run-php-tool.mjs phpcs --standard=phpcs.xml.dist",
    "fix:php": "node scripts/run-php-tool.mjs phpcbf --standard=phpcs.xml.dist",
    "test:php": "node scripts/run-php-tool.mjs phpunit --configuration phpunit.xml.dist",
    "lint": "npm run lint:js && npm run lint:css && npm run lint:php",
    "test": "npm run test:js && npm run test:php"
  }
}
```

- [ ] **Step 5: Verify and commit**

Run:

```powershell
npm run lint:php
npm run test:php
```

At this transition point PHPUnit may still use Composer's existing test bootstrap, but both commands must execute the npm-provisioned binaries.

Commit:

```powershell
git add scripts/run-php-tool.mjs tests/scripts/run-php-tool.test.js package.json package-lock.json phpcs.xml.dist
git commit -m "build: run PHP quality tools through npm"
```

## Task 3: Add the production PHP autoloader

**Files:**
- Create: `includes/Autoloader.php`
- Create: `tests/php/AutoloaderTest.php`
- Modify: `fancyn-plugin-starter.php`
- Modify: `scripts/rename-plugin.mjs`
- Modify: `tests/scripts/rename-plugin.test.js`

- [ ] **Step 1: Write failing autoloader tests**

Test exact namespace matching, nested class resolution, unrelated namespace rejection, traversal-like class rejection, and missing files:

```php
public function test_resolves_only_project_namespace_inside_includes(): void {
	$root = $this->fixture_with_class( 'Example\\Starter\\Core\\Demo', 'Core/Demo.php' );
	$loader = new \Fancyn\PluginStarter\Autoloader( 'Example\\Starter', $root );

	$this->assertTrue( $loader->load( 'Example\\Starter\\Core\\Demo' ) );
	$this->assertFalse( $loader->load( 'Other\\Core\\Demo' ) );
	$this->assertFalse( $loader->load( 'Example\\Starter\\..\\Secrets' ) );
}
```

- [ ] **Step 2: Verify RED**

Run `npm run test:php -- --filter AutoloaderTest`.

Expected: FAIL because `Autoloader` does not exist.

- [ ] **Step 3: Implement and register the loader**

Create a final class with:

```php
public function __construct( string $namespace, string $base_directory );
public function register(): void;
public function load( string $class ): bool;
```

Normalize the configured namespace with one trailing backslash, reject empty path segments and `.`/`..`, build `<base>/<relative>.php`, compare the normalized directory prefix before requiring, and return whether the class file was included.

Replace:

```php
require_once FANCYN_PLUGIN_STARTER_PATH . 'vendor/autoload.php';
```

with a direct include of `includes/Autoloader.php`, construction for `Fancyn\PluginStarter`, and `register()`.

- [ ] **Step 4: Make rename update the loader namespace**

Extend rename fixtures to assert `includes/Autoloader.php` contains the renamed namespace contract and the packaged renamed plugin activates without Composer.

- [ ] **Step 5: Verify and commit**

Run:

```powershell
npm run test:php -- --filter AutoloaderTest
npx vitest run tests/scripts/rename-plugin.test.js
npm run lint:php
```

Commit:

```powershell
git add includes/Autoloader.php fancyn-plugin-starter.php tests/php/AutoloaderTest.php scripts/rename-plugin.mjs tests/scripts/rename-plugin.test.js
git commit -m "feat: add project-owned PHP autoloader"
```

## Task 4: Build the WordPress PHPUnit shim

**Files:**
- Create: `tests/php/support/WordPress.php`
- Create: `tests/php/support/WordPressTestCase.php`
- Create: `tests/php/support/WordPressShimTest.php`
- Modify: `tests/php/bootstrap.php`

- [ ] **Step 1: Write shim contract tests**

The public test API is:

```php
WordPressTestCase::stub( string $function, mixed $return_value ): void;
WordPressTestCase::callback( string $function, callable $callback ): void;
WordPressTestCase::calls( string $function ): array;
WordPressTestCase::assertCalledTimes( string $function, int $times ): void;
```

Test return values, callback arguments, call recording, automatic reset, JSON response exceptions, and `wp_die` exceptions.

- [ ] **Step 2: Verify RED**

Run `npm run test:php -- --filter WordPressShimTest`.

Expected: FAIL because the shim files do not exist.

- [ ] **Step 3: Implement isolated shim state**

Use a namespaced registry class with `stub()`, `callback()`, `invoke()`, `calls()`, and `reset()`. Global WordPress functions in `WordPress.php` delegate to the registry only when WordPress has not already defined them. Implement only functions referenced by production classes and existing tests. Represent `wp_send_json_success`, `wp_send_json_error`, and `wp_die` with typed exceptions carrying status and payload so tests can assert termination without ending PHPUnit.

- [ ] **Step 4: Load shim and project autoloader in test bootstrap**

`tests/php/bootstrap.php` must load the shim, load `includes/Autoloader.php`, register the project namespace, and contain no reference to `vendor/`.

- [ ] **Step 5: Verify and commit**

Run:

```powershell
npm run test:php -- --filter WordPressShimTest
npm run lint:php
```

Commit:

```powershell
git add tests/php/support/WordPress.php tests/php/support/WordPressTestCase.php tests/php/support/WordPressShimTest.php tests/php/bootstrap.php
git commit -m "test: add lightweight WordPress function shim"
```

## Task 5: Migrate PHP tests off Brain Monkey and Mockery

**Files:**
- Modify: `tests/php/core/*.php`
- Modify: `tests/php/admin/*.php`
- Modify: `tests/php/support/*.php`
- Modify: `tests/php/ui/*.php`
- Modify: `tests/php/http/*.php`
- Modify: `phpunit.xml.dist`

- [ ] **Step 1: Migrate low-coupling Support and UI suites**

Change tests to extend `WordPressTestCase`, replace `Functions\when()` with `stub()`/`callback()`, and replace `Functions\expect()` with assertions against `calls()`. Run:

```powershell
npm run test:php -- --testsuite support
npm run test:php -- --testsuite ui
```

Expected: both suites pass without importing `Brain\Monkey` or `Mockery`.

- [ ] **Step 2: Migrate Core and Admin suites**

Preserve assertions for lifecycle requirements, hook registration, screen-scoped assets, module tags, Settings API sanitization, admin pages, and WordPress integrations. Run:

```powershell
npm run test:php -- --testsuite core
npm run test:php -- --testsuite admin
```

- [ ] **Step 3: Migrate REST and AJAX suites**

Use the typed JSON termination exception to assert status 200/401/403/422 payloads. Preserve nonce-before-capability ordering and the absence of a `nopriv` action. Run:

```powershell
npm run test:php -- --testsuite http
```

- [ ] **Step 4: Prove dependency removal**

Run:

```powershell
rg -n "Brain\\\\Monkey|Mockery|vendor/autoload|composer" tests phpunit.xml.dist fancyn-plugin-starter.php includes
```

Expected: no dependency references; documentation mentions are handled later.

- [ ] **Step 5: Run complete PHP gates and commit**

Run:

```powershell
npm run lint:php
npm run test:php
```

Expected: all current PHP behavior tests pass with an assertion count at least equal to the pre-migration baseline of 410.

Commit:

```powershell
git add tests/php phpunit.xml.dist
git commit -m "test: remove Composer-based PHP test dependencies"
```

## Task 6: Remove Composer from source and packaging

**Files:**
- Delete: `composer.json`
- Delete: `composer.lock`
- Modify: `scripts/build-zip.mjs`
- Modify: `tests/scripts/build-zip.test.js`
- Modify: `.distignore`
- Modify: `.gitignore`
- Modify: `package.json`

- [ ] **Step 1: Write failing npm-only package tests**

Update the package fixture to contain forbidden `composer.json`, `composer.lock`, `vendor/autoload.php`, and `tools/phpunit/phpunit.phar`. Assert all are absent, `includes/Autoloader.php` is present, and the ZIP builder never invokes Composer.

- [ ] **Step 2: Verify RED**

Run `npx vitest run tests/scripts/build-zip.test.js`.

Expected: FAIL because the builder still creates/restores Composer production autoload files.

- [ ] **Step 3: Remove Composer staging logic**

Delete all Composer spawning, vendor backup/restore, and production install code from `build-zip.mjs`. Stage runtime files directly through `.distignore`, require `includes/Autoloader.php`, and fail if staged paths contain `vendor/`, `tools/`, or Composer manifests.

- [ ] **Step 4: Remove manifests and stale ignores**

Delete Composer files, remove `/vendor/` from the development setup documentation, retain `vendor/` in `.distignore` as a defensive exclusion, and add a package-script preflight that fails when tracked Composer manifests exist.

- [ ] **Step 5: Verify and commit**

Run:

```powershell
npx vitest run tests/scripts/build-zip.test.js
npm run test:php
npm run package
```

Inspect the ZIP and verify the autoloader exists and Composer/tool paths do not.

Commit:

```powershell
git add -A composer.json composer.lock scripts/build-zip.mjs tests/scripts/build-zip.test.js .distignore .gitignore package.json package-lock.json
git commit -m "build: remove Composer from runtime and packaging"
```

## Task 7: Refactor the rename engine for interactive use

**Files:**
- Modify: `scripts/rename-plugin.mjs`
- Modify: `tests/scripts/rename-plugin.test.js`

- [ ] **Step 1: Write failing reusable-engine tests**

Require exports:

```js
export function deriveMetadata(input) {}
export function validateMetadata(metadata) {}
export async function renameProject({ root, metadata, filesystem } = {}) {}
```

Test derived text domain, option prefix, REST namespace, and main filename; test validation; test identical reruns; test rejection of a different second initialization; test rollback after an injected staged-write failure.

- [ ] **Step 2: Verify RED**

Run `npx vitest run tests/scripts/rename-plugin.test.js`.

Expected: FAIL because the current CLI does not expose the complete reusable interface.

- [ ] **Step 3: Separate engine from JSON CLI**

Keep `--config` behavior, but route both CLI and future interactive init through the exported derivation, validation, staging, placeholder scan, and atomic apply functions. Persist resolved metadata to `plugin.json` and use it as the idempotency marker.

- [ ] **Step 4: Expand Composer-remnant scans**

After rename, reject `composer.json`, `composer.lock`, `vendor/autoload.php`, old namespace, old slug, old PHP prefix, old CSS prefix, and old REST namespace anywhere outside explicitly ignored generated directories.

- [ ] **Step 5: Verify and commit**

Run:

```powershell
npx vitest run tests/scripts/rename-plugin.test.js
npm run lint:js
```

Commit:

```powershell
git add scripts/rename-plugin.mjs tests/scripts/rename-plugin.test.js
git commit -m "refactor: expose atomic project rename engine"
```

## Task 8: Add interactive `npm run init`

**Files:**
- Create: `scripts/init-plugin.mjs`
- Create: `tests/scripts/init-plugin.test.js`
- Modify: `package.json`
- Modify: `package-lock.json`

- [ ] **Step 1: Write failing prompt-flow tests**

Inject a prompt adapter so tests provide answers without a terminal. Cover accepted defaults, invalid slug, invalid namespace, invalid prefixes, user cancellation, confirmation summary, successful rename delegation, and operation before `node_modules` exists.

```js
const answers = ['Example Plugin', 'example-plugin', 'Vendor\\ExamplePlugin', 'example_plugin_', 'ep-', true];
const prompt = vi.fn(async () => answers.shift());
const renameProject = vi.fn(async () => ({ changed: 42 }));
const result = await initializeProject({ root, prompt, renameProject });
expect(renameProject).toHaveBeenCalledWith(expect.objectContaining({ metadata: expect.objectContaining({ textDomain: 'example-plugin', restNamespace: 'example-plugin/v1' }) }));
expect(result.confirmed).toBe(true);
```

- [ ] **Step 2: Verify RED**

Run `npx vitest run tests/scripts/init-plugin.test.js`.

Expected: FAIL because the init module is missing.

- [ ] **Step 3: Implement dependency-free prompts**

Use `node:readline/promises`, `process.stdin`, and `process.stdout`. Export:

```js
export async function initializeProject({ root = process.cwd(), prompt = terminalPrompt, renameProject: rename = renameProject } = {}) {}
```

Prompt in the approved order, show resolved metadata as JSON, require an explicit yes/no confirmation, return `{ confirmed: false, changed: 0 }` on cancellation, and never call rename before confirmation.

- [ ] **Step 4: Add npm command**

```json
{
  "scripts": {
    "init": "node scripts/init-plugin.mjs"
  }
}
```

Ensure the script imports no package outside Node built-ins and `scripts/rename-plugin.mjs`.

- [ ] **Step 5: Verify and commit**

Run:

```powershell
npx vitest run tests/scripts/init-plugin.test.js tests/scripts/rename-plugin.test.js
npm run lint:js
```

Commit:

```powershell
git add scripts/init-plugin.mjs tests/scripts/init-plugin.test.js package.json package-lock.json
git commit -m "feat: add interactive npm project initializer"
```

## Task 9: Add explicit npm-only regression gates

**Files:**
- Create: `tests/scripts/npm-only.test.js`
- Modify: `scripts/validate-build.mjs`
- Modify: `tests/scripts/validate-build.test.js`
- Modify: `package.json`

- [ ] **Step 1: Write failing source and command-contract tests**

Assert:

- Composer manifests are absent.
- Runtime PHP contains no `vendor/autoload.php`.
- npm scripts contain no `composer` command.
- ZIP/build validation rejects `vendor/`, `tools/`, and Composer manifests.
- `init-plugin.mjs` has no bare-package imports.
- `npm run lint`, `npm test`, and `npm run package` are the canonical gates.

- [ ] **Step 2: Verify RED**

Run:

```powershell
npx vitest run tests/scripts/npm-only.test.js tests/scripts/validate-build.test.js
```

- [ ] **Step 3: Extend build validation**

Make validation scan project source and staged release paths for Composer artifacts. Error messages must identify the exact forbidden path and say that the project is npm-only.

- [ ] **Step 4: Test without Composer on PATH**

Spawn each canonical npm command in a fixture environment whose `PATH` contains Node, npm, and PHP wrappers but no Composer executable. Use lightweight fixture commands for the process-contract test; the clean-clone acceptance task runs the real commands.

- [ ] **Step 5: Verify and commit**

Run:

```powershell
npx vitest run tests/scripts/npm-only.test.js tests/scripts/validate-build.test.js
npm run lint
npm test
```

Commit:

```powershell
git add tests/scripts/npm-only.test.js scripts/validate-build.mjs tests/scripts/validate-build.test.js package.json package-lock.json
git commit -m "test: enforce npm-only project boundaries"
```

## Task 10: Rewrite npm-only documentation

**Files:**
- Modify: `README.md`
- Modify: `tests/acceptance/package-smoke.md`
- Modify: `docs/WORDPRESS-PLUGIN-STARTER-SPEC.md`

- [ ] **Step 1: Update the primary workflow**

Document exactly:

```bash
git clone <repository-url>
cd fancyn-plugin-starter
npm run init
npm install
npm run lint
npm test
npm run package
```

State that PHP 8.1+, Node 20.19+, and npm 10+ are required; Composer is unsupported and unnecessary.

- [ ] **Step 2: Document PHP tool setup**

Explain `postinstall`, `tools/` cache reuse, pinned checksums, deleting `tools/` to force refresh, the need for network access on the first install, offline reuse of a valid cache, checksum mismatch handling, and proxy/firewall troubleshooting.

- [ ] **Step 3: Document interactive init**

Show the five prompts, derived values, confirmation step, validation rules, cancellation behavior, idempotent rerun, and JSON automation fallback.

- [ ] **Step 4: Update acceptance checklist**

Add clean-clone npm-only checks, `composer`-absent PATH evidence, renamed project ZIP checks, and explicit ZIP assertions for no Composer/vendor/tools content.

- [ ] **Step 5: Update the authoritative product spec**

Replace Composer architecture/tooling references with the approved npm provisioner, internal autoloader, shim, and init workflow. Preserve WordPress Coding Standards as mandatory.

- [ ] **Step 6: Scan and commit**

Run:

```powershell
rg -n "composer install|composer test|vendor/autoload" README.md docs tests/acceptance
```

Expected: no obsolete user instructions; historical npm-only design text may mention removed Composer behavior only as rationale or exclusion.

Commit:

```powershell
git add README.md tests/acceptance/package-smoke.md docs/WORDPRESS-PLUGIN-STARTER-SPEC.md
git commit -m "docs: document npm-only starter workflow"
```

## Task 11: Verify clean-clone initialization and renamed packaging

**Files:**
- Modify: `tests/acceptance/package-smoke.md`
- Generated only: `artifacts/npm-only-clean-*`

- [ ] **Step 1: Create a clean Windows clone without generated dependencies**

Use `git clone --local --no-hardlinks` into a unique ignored `artifacts/npm-only-clean-<timestamp>` directory. Confirm `node_modules/`, `tools/`, `vendor/`, and build outputs are absent.

- [ ] **Step 2: Initialize through injected terminal input**

Run `npm run init` with a test project named `Example Deployment Plugin`, slug `example-deployment-plugin`, namespace `Example\DeploymentPlugin`, PHP prefix `example_deployment_plugin_`, and CSS prefix `edp-`; confirm yes.

Expected: main file becomes `example-deployment-plugin.php`, derived values match the design, and no starter placeholders or Composer files remain.

- [ ] **Step 3: Run the complete Windows workflow**

Run:

```powershell
npm install
npm run lint
npm test
npm run package
```

Expected: all commands exit 0 when `composer` is unavailable; ZIP is `build/example-deployment-plugin.zip`.

- [ ] **Step 4: Run Unix-like workflow**

Use an available CI runner, WSL distribution, or Linux container with the clean clone. Run the same npm commands with Composer absent. Record OS, Node, npm, and PHP versions. If no Unix-like runtime exists locally, add and execute a GitHub Actions Linux job before marking this task complete.

- [ ] **Step 5: Audit the renamed ZIP**

Expand the ZIP and verify:

- One `example-deployment-plugin/` root.
- Internal `includes/Autoloader.php` present.
- All packaged PHP files pass `php -l`.
- No `vendor/`, `tools/`, Composer manifests, tests, scripts, source assets, or source maps.
- SHA-256 recorded.

- [ ] **Step 6: Commit acceptance evidence**

Update `tests/acceptance/package-smoke.md` with command results, platform versions, ZIP checksum, and any genuine pending live checks.

```powershell
git add tests/acceptance/package-smoke.md
git commit -m "test: record npm-only clean-clone acceptance"
```

## Task 12: Perform final WordPress live acceptance

**Files:**
- Modify: `tests/acceptance/package-smoke.md`

- [ ] **Step 1: Install the renamed final ZIP**

On WordPress 6.5+ with PHP 8.1+, deactivate any development copy, install the renamed ZIP into an isolated plugin directory, activate it, and verify no warning, notice, or fatal output.

- [ ] **Step 2: Exercise production pages and persistence**

Verify Dashboard and Settings load compiled CSS/JS from the renamed plugin path, the Vite script uses `type="module"`, Settings API values persist, and editor/media/color dependencies load only on the pages that need them.

- [ ] **Step 3: Exercise development showcase**

Enable the renamed dev-mode constant, verify all 11 components, Tom Select, modal, tabs, dropdown, media dialog, color picker, editor, visible focus, Escape behavior, and 1440/768/375px layouts. Disable dev mode and verify the showcase URL returns 403.

- [ ] **Step 4: Exercise HTTP authorization**

Verify authorized REST and AJAX calls succeed, logged-out REST fails, missing/invalid AJAX nonce fails, and insufficient capability fails.

- [ ] **Step 5: Verify CSS isolation**

Open an unrelated WordPress Dashboard page and confirm zero renamed-plugin scripts/styles, unchanged WordPress layout, and no horizontal overflow.

- [ ] **Step 6: Run final release gate**

From the initialized project run:

```powershell
npm run lint
npm test
npm run build
npm run validate:build
npm run package
npm audit
git status --short
```

Expected: every command exits 0, audit reports no known vulnerabilities, acceptance rows are PASS, and the intended branch is clean.

- [ ] **Step 7: Commit final evidence**

```powershell
git add tests/acceptance/package-smoke.md
git commit -m "docs: complete npm-only WordPress acceptance"
```

---

## Requirement traceability

| Design requirement | Implemented by |
|---|---|
| No Composer commands, manifests, vendor runtime, or fallback | Tasks 3, 5, 6, 9 |
| npm-provisioned checksum-pinned PHP tools | Tasks 1–2 |
| Internal project PHP autoloader | Task 3 |
| Brain Monkey/Mockery replacement | Tasks 4–5 |
| Interactive init before npm install | Tasks 7–8 |
| Atomic/idempotent initialization | Tasks 7–8 |
| npm-only lint, test, build, and package | Tasks 2, 6, 9 |
| Composer-free production ZIP | Tasks 6, 9, 11 |
| Documentation and troubleshooting | Task 10 |
| Windows and Unix-like clean-clone proof | Task 11 |
| WordPress runtime, responsive, keyboard, API, and CSS isolation proof | Task 12 |

## Execution notes

- Execute tasks sequentially because later tasks depend on the provisioned tools and test shim.
- Do not delete Composer files until the npm-provisioned PHPUnit/PHPCS runners and replacement shim pass.
- Never accept a checksum from the same response being verified; pin digests obtained independently for exact versioned artifacts.
- Keep each implementation and review commit focused so failures can be bisected across the migration boundary.
- Do not mark the npm-only migration complete while any documented or executable workflow still calls Composer.
