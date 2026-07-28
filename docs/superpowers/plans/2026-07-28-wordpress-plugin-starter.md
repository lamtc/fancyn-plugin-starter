# WordPress Plugin Starter Kit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a reusable, independently distributable WordPress admin-plugin starter with PHP UI components, scoped Tailwind CSS, Vite, Alpine.js, Tom Select, secure WordPress API examples, project renaming, and validated ZIP packaging.

**Architecture:** A namespaced PHP bootstrap registers focused Core, Admin, UI, Support, and Http services. Vite builds one admin JavaScript graph and one scoped Tailwind stylesheet; PHP resolves either the Vite development server or its production manifest and enqueues assets only on plugin screens. Stateless PHP components render accessible native HTML, while Settings API and HTTP services own validation, permissions, and persistence.

**Tech Stack:** WordPress Plugin API, PHP 8.1+, Composer PSR-4, WordPress Coding Standards, PHPUnit 10, Brain Monkey, Node.js 20+, Vite 5, Tailwind CSS 3, `@tailwindcss/forms`, Alpine.js 3, Tom Select 2, Vitest, ESLint, Stylelint, Archiver.

---

## Preconditions and fixed decisions

- Work from `D:\laragon\www\plugin-dev\wp-content\plugins\fancyn-plugin-starter`.
- Initialize Git before execution because the current directory is not a repository.
- Use `Fancyn\PluginStarter` as the starter namespace and `fancyn_plugin_starter_` for global PHP identifiers.
- Use `fps-` as the starter Tailwind prefix and `.fps-plugin-root` as the CSS root. Both are renameable placeholders.
- Use `fancyn-plugin-starter` as plugin slug and text domain.
- Use WordPress Coding Standards, not PSR-12, for PHP formatting and naming.
- Support WordPress 6.5+ and PHP 8.1+.
- Keep the component showcase disabled by default unless `FANCYN_PLUGIN_STARTER_DEV_MODE` is explicitly true.
- Commit `vendor/` production dependencies into release ZIP staging, not into Git.

## Target file map

```text
fancyn-plugin-starter/
├── assets/src/css/{admin.css,components.css,utilities.css}
├── assets/src/js/{admin.js,alpine.js,tom-select.js,textarea.js,wordpress-api.js}
├── assets/dist/
├── includes/Admin/{Admin.php,Assets.php,Menu.php,Settings.php}
├── includes/Core/{Activator.php,Deactivator.php,Plugin.php,ServiceProvider.php}
├── includes/Http/{Ajax.php,Rest.php}
├── includes/Support/{Arr.php,Html.php,Validation.php,View.php}
├── includes/UI/{Button.php,Checkbox.php,FormField.php,FormSection.php,Input.php,
│   Label.php,Radio.php,Select.php,SwitchField.php,Textarea.php,ValidationMessage.php}
├── templates/admin/{dashboard.php,settings.php,components-demo.php}
├── templates/partials/{empty-state.php,notice.php,page-header.php}
├── scripts/{build-zip.mjs,rename-plugin.mjs,validate-build.mjs}
├── tests/php/{bootstrap.php,Core,Admin,Support,UI,Http}/
├── tests/js/{alpine.test.js,tom-select.test.js,textarea.test.js,wordpress-api.test.js}
├── tests/scripts/{rename-plugin.test.js,validate-build.test.js,build-zip.test.js}
├── composer.json, package.json, plugin.json
├── phpcs.xml.dist, phpunit.xml.dist, vite.config.js, tailwind.config.js
├── postcss.config.js, eslint.config.js, stylelint.config.js
├── fancyn-plugin-starter.php, uninstall.php, README.md, .distignore, .gitignore
└── docs/{WORDPRESS-PLUGIN-STARTER-SPEC.md,DESIGN-RULES.md}
```

## Task 1: Initialize repository and quality toolchain

**Files:**
- Create: `.gitignore`
- Create: `composer.json`
- Create: `package.json`
- Create: `phpcs.xml.dist`
- Create: `phpunit.xml.dist`
- Create: `eslint.config.js`
- Create: `stylelint.config.js`
- Create: `tests/php/bootstrap.php`

- [ ] **Step 1: Initialize Git and establish the baseline**

Run:

```powershell
git init
git add docs
git commit -m "docs: add starter requirements and design"
```

Expected: a root commit containing the two source specifications and approved design document.

- [ ] **Step 2: Add Composer configuration**

Define PHP `>=8.1`, PSR-4 mapping `Fancyn\\PluginStarter\\` to `includes/`, dev dependencies `phpunit/phpunit:^10.5`, `brain/monkey:^2.6`, `squizlabs/php_codesniffer:^3.10`, `wp-coding-standards/wpcs:^3.1`, and `phpcompatibility/php-compatibility:^9.3`. Add `allow-plugins.dealerdirect/phpcodesniffer-composer-installer=true` and scripts `lint:php` and `test:php`.

- [ ] **Step 3: Configure WordPress Coding Standards**

In `phpcs.xml.dist`, scan `fancyn-plugin-starter.php`, `uninstall.php`, `includes`, `templates`, and `tests`; exclude `vendor`, `assets/dist`, and `node_modules`; apply `WordPress-Core`, `WordPress-Docs`, `WordPress-Extra`, and `PHPCompatibilityWP`; set `minimum_supported_wp_version=6.5`, `testVersion=8.1-`, text domain `fancyn-plugin-starter`, and prefix list `fancyn_plugin_starter`.

In `phpunit.xml.dist`, define the named suites used later in this plan: `core` → `tests/php/Core`, `admin` → `tests/php/Admin`, `support` → `tests/php/Support`, `ui` → `tests/php/UI`, and `http` → `tests/php/Http`. Bootstrap through `tests/php/bootstrap.php`, fail on warnings/risky tests, and write coverage only when `XDEBUG_MODE=coverage` is supplied.

- [ ] **Step 4: Add Node quality configuration**

Define Node `>=20`, ESM mode, and dependencies for Vite, Tailwind, PostCSS, Autoprefixer, Alpine, Tom Select, Vitest, ESLint, Stylelint, Archiver, and Ignore. Add scripts:

```json
{
  "dev": "vite",
  "build": "vite build",
  "lint:js": "eslint assets/src/js scripts tests/js tests/scripts",
  "lint:css": "stylelint \"assets/src/css/**/*.css\"",
  "lint": "npm run lint:js && npm run lint:css && composer lint:php",
  "test:js": "vitest run",
  "test": "composer test:php && npm run test:js",
  "validate:build": "node scripts/validate-build.mjs",
  "package": "npm run lint && npm run test && npm run build && npm run validate:build && node scripts/build-zip.mjs",
  "rename": "node scripts/rename-plugin.mjs"
}
```

- [ ] **Step 5: Install dependencies and verify empty suites**

Run:

```powershell
composer install
npm install
composer lint:php
npm run lint:js
npm run lint:css
```

Expected: all three linters exit 0.

- [ ] **Step 6: Commit toolchain**

```powershell
git add .gitignore composer.json composer.lock package.json package-lock.json phpcs.xml.dist phpunit.xml.dist eslint.config.js stylelint.config.js tests/php/bootstrap.php
git commit -m "chore: configure starter quality toolchain"
```

## Task 2: Build plugin bootstrap and lifecycle

**Files:**
- Create: `fancyn-plugin-starter.php`
- Create: `includes/Core/ServiceProvider.php`
- Create: `includes/Core/Plugin.php`
- Create: `includes/Core/Activator.php`
- Create: `includes/Core/Deactivator.php`
- Create: `uninstall.php`
- Test: `tests/php/Core/PluginTest.php`
- Test: `tests/php/Core/ActivatorTest.php`
- Test: `tests/php/Core/UninstallTest.php`

- [ ] **Step 1: Write failing bootstrap tests**

Cover these exact behaviors:

```php
public function test_plugin_registers_each_service_once(): void;
public function test_plugin_boots_each_service_once(): void;
public function test_activation_rejects_php_below_8_1(): void;
public function test_activation_rejects_wordpress_below_6_5(): void;
public function test_uninstall_deletes_only_the_prefixed_option(): void;
```

Use anonymous `ServiceProvider` test doubles with counters and Brain Monkey expectations for WordPress functions.

- [ ] **Step 2: Verify tests fail**

Run: `composer test:php -- --filter "PluginTest|ActivatorTest|UninstallTest"`

Expected: FAIL because Core classes and entry files do not exist.

- [ ] **Step 3: Implement the minimal lifecycle**

`ServiceProvider` exposes `register(): void` and `boot(): void`. `Plugin` accepts an array of providers, guards duplicate startup, then registers all before booting all. The main file defines version/path/url constants, loads `vendor/autoload.php`, registers activation/deactivation hooks, and starts the plugin on `plugins_loaded`.

`Activator` compares `PHP_VERSION` and `$GLOBALS['wp_version']`, calls `deactivate_plugins()` and `wp_die()` with translated messages when unsupported, and flushes rewrite rules only after passing checks. `Deactivator` flushes rewrite rules. `uninstall.php` exits unless `WP_UNINSTALL_PLUGIN` is defined and deletes only `fancyn_plugin_starter_settings`.

- [ ] **Step 4: Run tests and lint**

Run:

```powershell
composer test:php -- --filter "PluginTest|ActivatorTest|UninstallTest"
composer lint:php
```

Expected: PASS and 0 PHPCS errors.

- [ ] **Step 5: Commit bootstrap**

```powershell
git add fancyn-plugin-starter.php uninstall.php includes/Core tests/php/Core
git commit -m "feat: add plugin bootstrap and lifecycle"
```

## Task 3: Configure Tailwind design system and Vite build

**Files:**
- Create: `vite.config.js`
- Create: `tailwind.config.js`
- Create: `postcss.config.js`
- Create: `assets/src/css/admin.css`
- Create: `assets/src/css/components.css`
- Create: `assets/src/css/utilities.css`
- Create: `assets/src/js/admin.js`
- Create: `tests/scripts/vite-build.test.js`

- [ ] **Step 1: Write failing configuration tests**

Test that Tailwind exports prefix `fps-`, disables `corePlugins.preflight`, uses forms strategy `class`, scans PHP/templates/JS, and exposes every token from `DESIGN-RULES.md`: surface, ink, brand, success, warning, danger, info, line, `control`/`card` radius, and `card`/`popover` shadow. Test that Vite has `admin` and `styles` inputs, creates a manifest, disables source maps, and outputs into `assets/dist`.

- [ ] **Step 2: Verify tests fail**

Run: `npm run test:js -- tests/scripts/vite-build.test.js`

Expected: FAIL because Vite and Tailwind configurations do not exist.

- [ ] **Step 3: Implement configuration and scoped base styles**

Import Tailwind layers and the two local CSS modules from `admin.css`. Put only `.fps-plugin-root` typography/page-shell defaults and WordPress integration corrections in `components.css`; do not declare unscoped `button`, `input`, `table`, or heading selectors. Put accessible focus utilities, screen-reader helpers, reduced-motion handling, and responsive overflow guards in `utilities.css`.

- [ ] **Step 4: Build and inspect artifacts**

Run:

```powershell
npm run test:js -- tests/scripts/vite-build.test.js
npm run build
Get-ChildItem -Recurse assets/dist
```

Expected: tests PASS; manifest, minified CSS, and minified JavaScript exist; no `.map` files exist.

- [ ] **Step 5: Commit asset pipeline**

```powershell
git add vite.config.js tailwind.config.js postcss.config.js assets/src tests/scripts/vite-build.test.js
git commit -m "feat: add scoped Tailwind and Vite pipeline"
```

## Task 4: Resolve and enqueue development or production assets

**Files:**
- Create: `includes/Admin/Assets.php`
- Test: `tests/php/Admin/AssetsTest.php`

- [ ] **Step 1: Write failing asset tests**

Cover allowed hook filtering, current-screen prefix filtering, Vite health success, Vite failure fallback, manifest resolution, dynamic CSS imports, versioned production URLs, requested WordPress dependencies, and missing-manifest admin notice.

- [ ] **Step 2: Verify tests fail**

Run: `composer test:php -- --filter AssetsTest`

Expected: FAIL because `Assets` does not exist.

- [ ] **Step 3: Implement `Assets`**

Inject plugin URL/path, version, dev-server URL, and allowed-hook callback. Register `admin_enqueue_scripts` and `admin_notices`. In dev mode, make a short nonblocking request to `/@vite/client`; enqueue it plus source entries only on HTTP 200. Otherwise parse `assets/dist/.vite/manifest.json`, enqueue the admin entry and every listed CSS file, and use plugin version as fallback version. Provide an explicit per-page dependency map so `wp-color-picker`, `wp-editor`, `media-editor`, and `wp-api-fetch` load only where used.

- [ ] **Step 4: Run focused tests and PHPCS**

Run: `composer test:php -- --filter AssetsTest; composer lint:php`

Expected: PASS; unrelated admin hooks enqueue nothing.

- [ ] **Step 5: Commit asset service**

```powershell
git add includes/Admin/Assets.php tests/php/Admin/AssetsTest.php
git commit -m "feat: scope admin asset loading"
```

## Task 5: Add view rendering and safe HTML attributes

**Files:**
- Create: `includes/Support/Arr.php`
- Create: `includes/Support/Html.php`
- Create: `includes/Support/View.php`
- Test: `tests/php/Support/ArrTest.php`
- Test: `tests/php/Support/HtmlTest.php`
- Test: `tests/php/Support/ViewTest.php`

- [ ] **Step 1: Write failing support tests**

Test nested array reads with defaults; class-list flattening; boolean attributes; `data-*` and `aria-*`; rejection of event handlers and unsafe attribute names; context-correct escaping; template resolution inside the configured root; variable extraction; returned versus echoed view output; and path traversal rejection.

- [ ] **Step 2: Verify tests fail**

Run: `composer test:php -- --testsuite support`

Expected: FAIL because Support classes do not exist.

- [ ] **Step 3: Implement support helpers**

`Html::attributes()` accepts scalar values, safe names matching `^(aria|data)-[a-z0-9_-]+$` or an explicit native-attribute allowlist, emits boolean attributes by presence, joins class arrays, and escapes every value with `esc_attr()`. It must discard names beginning with `on`. `View` resolves only real paths below `templates/`, uses output buffering, and exposes `make()` and `render()`.

- [ ] **Step 4: Verify support layer**

Run: `composer test:php -- --testsuite support; composer lint:php`

Expected: PASS and no unsafe attribute or traversal test failure.

- [ ] **Step 5: Commit support layer**

```powershell
git add includes/Support tests/php/Support
git commit -m "feat: add safe rendering helpers"
```

## Task 6: Build Label, ValidationMessage, and FormField foundation

**Files:**
- Create: `includes/UI/Label.php`
- Create: `includes/UI/ValidationMessage.php`
- Create: `includes/UI/FormField.php`
- Test: `tests/php/UI/LabelTest.php`
- Test: `tests/php/UI/ValidationMessageTest.php`
- Test: `tests/php/UI/FormFieldTest.php`

- [ ] **Step 1: Write failing component contract tests**

For every component assert that `make()` returns escaped HTML and `render()` echoes the identical string. Verify Label `for`, required marker, optional text; four message variants with text plus optional allowlisted SVG and `role="alert"` for submit errors; FormField ordering Label → Control → Description → ValidationMessage, generated IDs, merged `aria-describedby`, and `aria-invalid=true` on error.

- [ ] **Step 2: Verify tests fail**

Run: `composer test:php -- --filter "LabelTest|ValidationMessageTest|FormFieldTest"`

Expected: FAIL because the three classes do not exist.

- [ ] **Step 3: Implement the three renderers**

Use semantic Tailwind token classes only. Filter icon HTML with a shared SVG `wp_kses()` allowlist. Ensure error IDs are deterministic from control ID (`<id>-error`) and description IDs use `<id>-description`.

- [ ] **Step 4: Verify markup and lint**

Run: `composer test:php -- --filter "LabelTest|ValidationMessageTest|FormFieldTest"; composer lint:php`

Expected: PASS.

- [ ] **Step 5: Commit field foundation**

```powershell
git add includes/UI/Label.php includes/UI/ValidationMessage.php includes/UI/FormField.php tests/php/UI
git commit -m "feat: add accessible form field foundation"
```

## Task 7: Build Button and text controls

**Files:**
- Create: `includes/UI/Button.php`
- Create: `includes/UI/Input.php`
- Create: `includes/UI/Textarea.php`
- Test: `tests/php/UI/ButtonTest.php`
- Test: `tests/php/UI/InputTest.php`
- Test: `tests/php/UI/TextareaTest.php`

- [ ] **Step 1: Write failing renderer tests**

Button tests cover `primary`, `secondary`, `outline`, `ghost`, `danger`, `link`; `sm`, `md`, `lg`, `icon`; button/link native elements; disabled/loading behavior; fixed-width loading label; safe SVG icon; and mandatory `aria-label` for icon-only buttons. Input tests cover allowed types, hidden input without visual wrapper, required/disabled/readonly/error states, arbitrary safe attributes, and rejection of invalid types. Textarea tests cover rows, maxlength, character counter data, auto-resize data, and escaped content.

- [ ] **Step 2: Verify tests fail**

Run: `composer test:php -- --filter "ButtonTest|InputTest|TextareaTest"`

Expected: FAIL because renderers do not exist.

- [ ] **Step 3: Implement minimal renderers**

Map variants and sizes through fixed class arrays so request data can never become Tailwind classes. Controls use 40–44px heights, 10px radius, brand-soft focus ring, danger error ring, 14px text, and 12–14px horizontal padding from `DESIGN-RULES.md`. `loading=true` implies disabled and adds `aria-busy=true`.

- [ ] **Step 4: Run tests, PHPCS, and production build**

Run: `composer test:php -- --filter "ButtonTest|InputTest|TextareaTest"; composer lint:php; npm run build`

Expected: PASS and generated CSS contains prefixed component utilities.

- [ ] **Step 5: Commit text controls**

```powershell
git add includes/UI/Button.php includes/UI/Input.php includes/UI/Textarea.php tests/php/UI assets/src/css
git commit -m "feat: add button and text controls"
```

## Task 8: Build choice controls and enhanced select contract

**Files:**
- Create: `includes/UI/Select.php`
- Create: `includes/UI/Checkbox.php`
- Create: `includes/UI/Radio.php`
- Create: `includes/UI/SwitchField.php`
- Test: `tests/php/UI/SelectTest.php`
- Test: `tests/php/UI/CheckboxTest.php`
- Test: `tests/php/UI/RadioTest.php`
- Test: `tests/php/UI/SwitchFieldTest.php`

- [ ] **Step 1: Write failing choice-control tests**

Select tests cover placeholder, scalar options, optgroups, selected scalar/array values, multiple naming, disabled options, and `data-enhanced-select` only when explicitly enhanced. Checkbox tests cover checked, disabled, label, description, native value, and indeterminate data. Radio tests cover a single option and a group with one checked value. Switch tests prove the output contains a real checkbox, accessible name, checked/disabled states, and no Alpine dependency.

- [ ] **Step 2: Verify tests fail**

Run: `composer test:php -- --filter "SelectTest|CheckboxTest|RadioTest|SwitchFieldTest"`

Expected: FAIL because renderers do not exist.

- [ ] **Step 3: Implement native-first controls**

Use native inputs, semantic labels, 16–18px checkbox/radio size, and a 40–44 × 22–24px switch track. Enhanced select emits configuration through individual `data-*` attributes for searchable, clearable, create, and placeholder; do not implement remote adapters in v1.0.

- [ ] **Step 4: Verify controls and build CSS**

Run: `composer test:php -- --filter "SelectTest|CheckboxTest|RadioTest|SwitchFieldTest"; composer lint:php; npm run build`

Expected: PASS.

- [ ] **Step 5: Commit choice controls**

```powershell
git add includes/UI/Select.php includes/UI/Checkbox.php includes/UI/Radio.php includes/UI/SwitchField.php tests/php/UI assets/src/css
git commit -m "feat: add choice form controls"
```

## Task 9: Build FormSection and complete UI contract coverage

**Files:**
- Create: `includes/UI/FormSection.php`
- Test: `tests/php/UI/FormSectionTest.php`
- Create: `tests/php/UI/ComponentContractTest.php`

- [ ] **Step 1: Write failing section and shared-contract tests**

Test `card`, `flat`, `divided`, and `compact`; title, description, content, optional filtered actions; and semantic heading level. The shared contract data provider must instantiate all eleven components and verify `make()`/`render()` parity, malicious text escaping, safe custom attributes, no unprefixed Tailwind utility class, and no business-data reads.

- [ ] **Step 2: Verify tests fail**

Run: `composer test:php -- --filter "FormSectionTest|ComponentContractTest"`

Expected: FAIL because FormSection and/or shared contracts are incomplete.

- [ ] **Step 3: Implement FormSection and normalize shared APIs**

Use 20–28px card padding, 16–20px title/content gap, 20–24px field gap, 18px radius, semantic surface/line/shadow tokens, and one action area. Correct earlier components until all use the same static `make(array $args): string` and `render(array $args): void` contract.

- [ ] **Step 4: Run the complete PHP UI suite**

Run: `composer test:php -- --testsuite ui; composer lint:php`

Expected: all eleven components PASS contract and focused tests.

- [ ] **Step 5: Commit UI kit**

```powershell
git add includes/UI tests/php/UI assets/src/css
git commit -m "feat: complete PHP form component kit"
```

## Task 10: Add admin menu, page shell, and Settings API example

**Files:**
- Create: `includes/Admin/Menu.php`
- Create: `includes/Admin/Settings.php`
- Create: `includes/Admin/Admin.php`
- Create: `includes/Support/Validation.php`
- Create: `templates/admin/dashboard.php`
- Create: `templates/admin/settings.php`
- Create: `templates/partials/page-header.php`
- Create: `templates/partials/notice.php`
- Create: `templates/partials/empty-state.php`
- Test: `tests/php/Admin/MenuTest.php`
- Test: `tests/php/Admin/SettingsTest.php`
- Test: `tests/php/Support/ValidationTest.php`

- [ ] **Step 1: Write failing admin tests**

Test `manage_options`, menu/page hook capture, dev-only showcase registration, setting name/group, field-specific sanitization for text/email/url/integer/boolean/color/attachment ID, invalid value fallback, translated notice, and prepared view model. Verify templates do not reference `$_POST`, `$_GET`, or update functions.

- [ ] **Step 2: Verify tests fail**

Run: `composer test:php -- --filter "MenuTest|SettingsTest|ValidationTest"`

Expected: FAIL because services and templates do not exist.

- [ ] **Step 3: Implement admin services and templates**

Register a top-level starter menu with dashboard and settings; register the component demo under Tools only when the dev constant is true. Use Settings API for `fancyn_plugin_starter_settings`, one sanitize callback that delegates by declared field schema, and standard settings errors/notices. Wrap every template in `#fancyn-plugin-starter-app.fps-plugin-root`, use the prescribed gray shell and white cards, and keep long form text at 720–800px.

- [ ] **Step 4: Verify admin behavior**

Run: `composer test:php -- --filter "MenuTest|SettingsTest|ValidationTest"; composer lint:php; npm run build`

Expected: PASS.

- [ ] **Step 5: Register providers in bootstrap and commit**

```powershell
git add includes/Admin includes/Support/Validation.php includes/Core/Plugin.php templates tests/php/Admin tests/php/Support/ValidationTest.php
git commit -m "feat: add admin shell and secure settings example"
```

## Task 11: Add Alpine interactions and textarea behavior

**Files:**
- Create: `assets/src/js/alpine.js`
- Create: `assets/src/js/textarea.js`
- Test: `tests/js/alpine.test.js`
- Test: `tests/js/textarea.test.js`
- Modify: `assets/src/js/admin.js`

- [ ] **Step 1: Write failing JavaScript tests**

Using jsdom, test initialization only inside `.fps-plugin-root`; modal open/close, Escape, focus trap and focus restoration; tabs arrow/Home/End activation and ARIA state; dropdown Escape/outside click; textarea counter on input; auto-resize height update; and harmless no-op when targets are absent.

- [ ] **Step 2: Verify tests fail**

Run: `npm run test:js -- tests/js/alpine.test.js tests/js/textarea.test.js`

Expected: FAIL because modules do not exist.

- [ ] **Step 3: Implement focused modules**

Export Alpine data factories `modal`, `tabs`, and `dropdown`; register them before one scoped Alpine start. Export `initTextareas(root)` and return cleanup callbacks for listeners. Respect `prefers-reduced-motion`; use 150–200ms transitions only for color, background, border, opacity, or transform.

- [ ] **Step 4: Verify JS and production bundle**

Run: `npm run test:js -- tests/js/alpine.test.js tests/js/textarea.test.js; npm run lint:js; npm run build`

Expected: PASS; bundle has no global custom variables.

- [ ] **Step 5: Commit interactions**

```powershell
git add assets/src/js tests/js
git commit -m "feat: add accessible Alpine interactions"
```

## Task 12: Integrate and theme Tom Select

**Files:**
- Create: `assets/src/js/tom-select.js`
- Modify: `assets/src/js/admin.js`
- Modify: `assets/src/css/components.css`
- Test: `tests/js/tom-select.test.js`

- [ ] **Step 1: Write failing initializer tests**

Test only `[data-enhanced-select]` targets initialize; repeated initialization is idempotent; searchable, clearable, multiple, create, and placeholder data map correctly; disabled elements stay disabled; destruction cleanup works; and ordinary selects are untouched.

- [ ] **Step 2: Verify tests fail**

Run: `npm run test:js -- tests/js/tom-select.test.js`

Expected: FAIL because initializer does not exist.

- [ ] **Step 3: Implement initializer and scoped theme**

Export `initTomSelects(root)`. Import only needed Tom Select CSS and override it below `.fps-plugin-root` so control height, 10px radius, 14px typography, focus ring, option states, tags, and popover shadow match native select. Do not add a remote adapter.

- [ ] **Step 4: Verify integration**

Run: `npm run test:js -- tests/js/tom-select.test.js; npm run lint; npm run build`

Expected: PASS and no unscoped Tom Select selectors in authored CSS.

- [ ] **Step 5: Commit enhanced select**

```powershell
git add assets/src/js assets/src/css/components.css tests/js/tom-select.test.js
git commit -m "feat: integrate scoped Tom Select"
```

## Task 13: Integrate WordPress media, editor, and color picker

**Files:**
- Create: `assets/src/js/wordpress-api.js`
- Modify: `assets/src/js/admin.js`
- Modify: `assets/src/css/components.css`
- Modify: `templates/admin/settings.php`
- Test: `tests/js/wordpress-api.test.js`
- Test: `tests/php/Admin/WordPressApiMarkupTest.php`

- [ ] **Step 1: Write failing integration tests**

Test media selection stores attachment ID and preview URL, removal clears both, selection can be restricted to image/file, an existing frame is reused, color picker initializes only marked inputs, and missing WordPress globals fail gracefully. PHP markup tests assert correct data attributes, hidden attachment-ID field, escaped preview, `wp_editor()` call, and dependency requests for only the settings screen.

- [ ] **Step 2: Verify tests fail**

Run: `npm run test:js -- tests/js/wordpress-api.test.js; composer test:php -- --filter WordPressApiMarkupTest`

Expected: FAIL because integrations do not exist.

- [ ] **Step 3: Implement integrations and settings markup**

Export `initMediaFields(root)` and `initColorPickers(root)`. Store attachment IDs, never URL-only values. Render rich text with `wp_editor()` from the page controller/template boundary. Scope compatibility CSS for `.wp-picker-container` and editor wrappers below `.fps-plugin-root` without overriding global WordPress styles.

- [ ] **Step 4: Verify integrations**

Run: `npm run test:js -- tests/js/wordpress-api.test.js; composer test:php -- --filter WordPressApiMarkupTest; npm run lint; npm run build`

Expected: PASS.

- [ ] **Step 5: Commit WordPress APIs**

```powershell
git add assets/src templates/admin/settings.php includes/Admin/Assets.php tests/js tests/php/Admin
git commit -m "feat: add WordPress admin API integrations"
```

## Task 14: Add secure REST and AJAX examples

**Files:**
- Create: `includes/Http/Rest.php`
- Create: `includes/Http/Ajax.php`
- Test: `tests/php/Http/RestTest.php`
- Test: `tests/php/Http/AjaxTest.php`

- [ ] **Step 1: Write failing HTTP security tests**

REST tests assert namespace `fancyn-plugin-starter/v1`, non-public route, explicit `permission_callback`, `manage_options`, nonce authentication through WordPress REST cookie flow, schema validation, sanitization, stable error codes, and structured success response. AJAX tests assert authenticated action only, `check_ajax_referer`, capability check, typed sanitization, `wp_send_json_success`, and `wp_send_json_error` paths.

- [ ] **Step 2: Verify tests fail**

Run: `composer test:php -- --testsuite http`

Expected: FAIL because HTTP services do not exist.

- [ ] **Step 3: Implement non-public sample endpoints**

Register `POST /fancyn-plugin-starter/v1/validate-setting` with argument schema and permission callback. Register `wp_ajax_fancyn_plugin_starter_validate_setting`; do not register a `nopriv` action. Return codes `fancyn_plugin_starter_forbidden`, `fancyn_plugin_starter_invalid_nonce`, and `fancyn_plugin_starter_invalid_value` consistently.

- [ ] **Step 4: Verify HTTP suite and lint**

Run: `composer test:php -- --testsuite http; composer lint:php`

Expected: PASS, including all unauthorized cases.

- [ ] **Step 5: Register providers and commit**

```powershell
git add includes/Http includes/Core/Plugin.php tests/php/Http
git commit -m "feat: add secure REST and AJAX examples"
```

## Task 15: Build complete component showcase and responsive demo

**Files:**
- Create: `templates/admin/components-demo.php`
- Modify: `templates/admin/dashboard.php`
- Modify: `templates/admin/settings.php`
- Modify: `assets/src/css/components.css`
- Test: `tests/php/Admin/ComponentsDemoTest.php`

- [ ] **Step 1: Write failing demo coverage test**

Render the demo and assert presence of all eleven component classes and required states, six button variants, four sizes, native/Tom Select, validation variants, color/media/editor integration, modal, tabs, dropdown, one primary action per region, page root, translated visible strings, and no raw unprefixed Tailwind classes.

- [ ] **Step 2: Verify test fails**

Run: `composer test:php -- --filter ComponentsDemoTest`

Expected: FAIL because showcase is missing.

- [ ] **Step 3: Implement the demo pages**

Use a 12-column dashboard grid, settings 8/4 layout, one-column mobile fallback, 16px mobile/24px desktop padding, 20–24px card gaps, and at least three typography levels. Include save-pending text beside switches that require form submission. Use Dashicons or one consistent internal SVG style; do not use emoji, gradients, glass effects, neon, heavy shadows, or decorative fake charts.

- [ ] **Step 4: Run automated demo checks and build**

Run: `composer test:php -- --filter ComponentsDemoTest; composer lint:php; npm run lint; npm run build`

Expected: PASS.

- [ ] **Step 5: Perform browser acceptance on desktop and mobile**

Open the three plugin pages at 1440px, 768px, and 375px. Verify no horizontal overflow, WordPress sidebar respect, focus visibility, modal keyboard operation, Tom Select/native alignment, editor/color picker layout, and no stylesheet change on Dashboard → Home. Save screenshots under an untracked `artifacts/ui-acceptance/` directory.

- [ ] **Step 6: Commit demo surfaces**

```powershell
git add templates assets/src/css/components.css tests/php/Admin/ComponentsDemoTest.php
git commit -m "feat: add responsive admin UI showcase"
```

## Task 16: Implement safe project rename workflow

**Files:**
- Create: `plugin.json`
- Create: `scripts/rename-plugin.mjs`
- Test: `tests/scripts/rename-plugin.test.js`

- [ ] **Step 1: Write failing rename tests in temporary fixtures**

Cover valid metadata; slug/text-domain validation; PHP namespace validation; PHP/CSS prefix validation; replacement in PHP, JS, CSS, JSON, Markdown, and config files; main-file rename; root ID/class replacement; REST and option prefixes; binary and excluded-directory skipping; leftover-placeholder failure; same-metadata idempotency; and different-metadata refusal after customization.

- [ ] **Step 2: Verify tests fail**

Run: `npm run test:js -- tests/scripts/rename-plugin.test.js`

Expected: FAIL because configuration and script do not exist.

- [ ] **Step 3: Implement canonical metadata and atomic rename**

Use these keys: `pluginName`, `pluginSlug`, `textDomain`, `namespace`, `phpPrefix`, `cssPrefix`, `optionPrefix`, `restNamespace`. Build changes in a temporary sibling directory, scan for every starter placeholder, then replace the source only after validation succeeds. Preserve `docs/superpowers/` as historical project documentation but update user-facing starter docs and source configuration.

- [ ] **Step 4: Verify rename behavior**

Run: `npm run test:js -- tests/scripts/rename-plugin.test.js; npm run lint:js`

Expected: PASS and test fixtures leave the working tree unchanged.

- [ ] **Step 5: Commit rename workflow**

```powershell
git add plugin.json scripts/rename-plugin.mjs tests/scripts/rename-plugin.test.js package.json
git commit -m "feat: add atomic plugin rename workflow"
```

## Task 17: Validate production build and package ZIP

**Files:**
- Create: `.distignore`
- Create: `scripts/validate-build.mjs`
- Create: `scripts/build-zip.mjs`
- Test: `tests/scripts/validate-build.test.js`
- Test: `tests/scripts/build-zip.test.js`

- [ ] **Step 1: Write failing build and packaging tests**

Build validation tests cover manifest existence/JSON shape, referenced files, no maps, no unprefixed authored CSS selectors, and required entry points. ZIP tests cover name from plugin slug, required main file/assets/vendor autoload, `.distignore` exclusions, absence of `node_modules`, `.git`, tests, scripts, source assets, maps, configs, package files, environment files, and deterministic replacement of an existing ZIP.

- [ ] **Step 2: Verify tests fail**

Run: `npm run test:js -- tests/scripts/validate-build.test.js tests/scripts/build-zip.test.js`

Expected: FAIL because scripts do not exist.

- [ ] **Step 3: Implement `.distignore` as the sole exclusion source**

List every exclusion from section 16 of `WORDPRESS-PLUGIN-STARTER-SPEC.md`, plus `docs/superpowers`, coverage, artifacts, ZIP output, and local editor files. Keep runtime templates, compiled assets, languages, main PHP file, uninstall file, README, and Composer production autoload.

- [ ] **Step 4: Implement validation and ZIP staging**

`validate-build.mjs` parses the manifest, checks files and source maps, and exits nonzero with one message per defect. `build-zip.mjs` runs `composer install --no-dev --optimize-autoloader` in a temporary staging directory, applies `.distignore` with Gitignore semantics, validates required runtime contents, archives the top-level plugin directory, and always removes staging in `finally`.

- [ ] **Step 5: Verify package pipeline**

Run:

```powershell
npm run test:js -- tests/scripts/validate-build.test.js tests/scripts/build-zip.test.js
npm run package
```

Expected: all gates PASS and `build/fancyn-plugin-starter.zip` exists.

- [ ] **Step 6: Inspect ZIP contents**

Run:

```powershell
Expand-Archive build/fancyn-plugin-starter.zip -DestinationPath artifacts/package-check -Force
rg --files artifacts/package-check
```

Expected: only runtime files are listed; the forbidden paths asserted by tests are absent.

- [ ] **Step 7: Commit packaging**

```powershell
git add .distignore scripts/validate-build.mjs scripts/build-zip.mjs tests/scripts package.json
git commit -m "feat: add validated ZIP packaging"
```

## Task 18: Add documentation and final acceptance gates

**Files:**
- Create: `README.md`
- Create: `languages/.gitkeep`
- Create: `tests/acceptance/css-isolation.test.js`
- Create: `tests/acceptance/package-smoke.md`
- Modify: `.gitignore`
- Modify: `package.json`

- [ ] **Step 1: Write the acceptance checklist before documentation**

In `tests/acceptance/package-smoke.md`, record exact clean-site steps: create clean WordPress 6.5+ site, install ZIP, activate, enable dev-mode constant, open all demo pages, save settings, exercise each WordPress API, call authorized and unauthorized REST/AJAX samples, disable dev mode, and confirm showcase disappearance.

- [ ] **Step 2: Add automated CSS isolation test**

Parse built CSS and fail on unscoped authored element selectors, Tailwind Preflight signatures, missing `fps-` utilities, global WordPress selectors, or source maps. Permit third-party Tom Select rules only when the built selector is anchored by `.fps-plugin-root`.

- [ ] **Step 3: Write README**

Document requirements, `composer install`, `npm install`, dev server URL/configuration, build, lint, tests, rename JSON example, package, folder boundaries, component usage examples, Settings API flow, conditional WordPress dependencies, translation extraction, security rules, clean-site verification, and the explicit v1.0 exclusions.

- [ ] **Step 4: Run complete automated verification**

Run:

```powershell
composer install
npm install
npm run lint
npm run test
npm run build
npm run validate:build
npm run package
git status --short
```

Expected: every command exits 0; only intentional README/acceptance changes are uncommitted before the final commit.

- [ ] **Step 5: Execute clean WordPress smoke test**

Follow `tests/acceptance/package-smoke.md`. Record WordPress version, PHP version, browser, ZIP checksum, each acceptance result, and any screenshot path in the document.

Expected: every criterion in sections 21–22 of `WORDPRESS-PLUGIN-STARTER-SPEC.md` and section 24 of `DESIGN-RULES.md` is marked PASS.

- [ ] **Step 6: Commit documentation and acceptance evidence**

```powershell
git add README.md languages tests/acceptance .gitignore package.json
git commit -m "docs: add starter usage and acceptance workflow"
```

- [ ] **Step 7: Confirm clean completion state**

Run:

```powershell
git status --short
git log --oneline --decorate -10
```

Expected: clean working tree and a sequence of focused commits matching Tasks 1–18.

## Requirement traceability

| Requirement group | Implemented by | Verified by |
|---|---|---|
| Standalone bootstrap, PHP 8.1+, PSR-4 | Tasks 1–2 | Core PHPUnit suite, clean activation |
| WordPress Coding Standards and security | Tasks 1, 5–10, 13–14 | PHPCS, unit tests, smoke test |
| Tailwind prefix, no Preflight, root scope | Tasks 3, 17–18 | Config tests, CSS isolation test |
| Vite HMR, manifest, fallback, minification | Tasks 3–4 | Build tests, Assets tests, manual HMR |
| Screen-only enqueue and conditional dependencies | Tasks 4, 10, 13 | Assets and integration tests |
| Eleven accessible PHP components | Tasks 5–9 | UI unit and shared contract suites |
| Design tokens, layout, states, responsive rules | Tasks 3, 6–9, 15 | Demo coverage and browser acceptance |
| Alpine modal, tabs, dropdown | Tasks 11, 15 | Vitest and keyboard acceptance |
| Tom Select | Tasks 8, 12, 15 | Renderer/initializer tests and visual acceptance |
| Media, editor, color picker | Tasks 13, 15 | JS/PHP integration tests and smoke test |
| Settings API | Task 10 | Settings tests and saved-option smoke test |
| Secure REST/AJAX | Task 14 | Authorized/unauthorized PHPUnit tests |
| Internationalization | Tasks 2, 6–10, 13–15 | PHPCS text-domain rules and demo inspection |
| Rename workflow | Task 16 | Temporary-fixture Vitest suite |
| Build and ZIP workflow | Tasks 17–18 | Packaging tests, ZIP inspection, clean install |
| Documentation | Task 18 | README checklist review |

## Execution notes

- Run each task independently and do not skip the failing-test step.
- If a WordPress integration is hard to unit-test faithfully, keep the unit boundary around generated markup/configuration and require the documented browser smoke test for the real API behavior.
- Do not add features listed as post-v1.0 except the modal, tabs, and dropdown demonstrations explicitly required by the product acceptance criteria.
- Do not weaken CSS isolation to simplify third-party styling; scope imported integration CSS during PostCSS/Vite processing or wrap every authored override under the plugin root.
- Before claiming completion, run the complete commands in Task 18 from a clean checkout and install the generated ZIP on a clean supported WordPress site.
