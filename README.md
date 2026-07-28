# Fancyn Plugin Starter

A standalone WordPress admin-plugin starter using PHP components, scoped Tailwind CSS, Vite, Alpine.js, Tom Select, and selected WordPress APIs. Each renamed project builds and packages independently; no runtime “core UI plugin” is required.

## Requirements

- WordPress 6.5 or newer
- PHP 8.1 or newer
- Node.js 20.19 or newer
- npm 10 or newer

Composer is unsupported and unnecessary. PHP remains a system runtime invoked by npm commands.

## Install for development

```bash
git clone <repository-url>
cd fancyn-plugin-starter
npm run init
npm install
npm run lint
npm test
npm run package
```

Run `npm run init` before `npm install` to name the new plugin. The initializer uses only Node built-ins, so no downloaded JavaScript packages are required yet.

The first `npm install` runs `postinstall` and downloads checksum-pinned PHPUnit, PHPCS, WordPress Coding Standards, and their standards dependencies into the ignored `tools/` cache. A later install reuses a valid cache, including offline. Delete `tools/` to force a refresh. The first setup requires HTTPS access to the pinned release URLs; if a proxy or firewall blocks a download, configure npm/system proxy access and retry. A checksum mismatch aborts setup without replacing a valid cached tool—never bypass it or calculate trust from the failed response.

Place the project in `wp-content/plugins`, activate **Fancyn Plugin Starter**, then open its Dashboard or Settings page.

To expose the component showcase during local development, define this before WordPress loads plugins:

```php
define( 'FANCYN_PLUGIN_STARTER_DEV_MODE', true );
```

The showcase appears at **Tools → Plugin UI Kit**. It is disabled by default.

## Development assets

```bash
npm run dev
```

Vite listens at `http://127.0.0.1:5173`. On plugin-owned screens, PHP checks the Vite client and uses HMR source entries when available. If the server is unavailable, it falls back to `assets/dist/.vite/manifest.json`.

Assets are never enqueued on unrelated WordPress admin pages. Tailwind uses the `fps-` prefix, disables Preflight, uses `@tailwindcss/forms` in class mode, and scopes final PostCSS output under `.fps-plugin-root`.

Create production assets with:

```bash
npm run build
npm run validate:build
```

Release builds are minified, hashed, include a Vite manifest, and contain no source maps.

## Quality commands

```bash
npm run lint
npm run test
npm run build
```

- PHP follows WordPress Coding Standards with documented namespaced filename exceptions.
- The checksum-pinned PHPUnit PHAR and repository-owned WordPress shim cover PHP services and components.
- Vitest covers JavaScript behavior and Node release scripts.
- ESLint and Stylelint check authored JavaScript and CSS.

## Folder boundaries

- `includes/` contains namespaced PHP application code; keep bootstrapping in `Core`, admin orchestration in `Admin`, HTTP endpoints in `Http`, and markup-only controls in `UI`.
- `templates/` contains plugin-owned page templates. Wrap plugin UI in `.fps-plugin-root`.
- `assets/src/` contains authored JavaScript and CSS; `assets/dist/` is generated and must not be edited by hand.
- `languages/` contains translation catalogs.
- `scripts/` contains rename, validation, and packaging tooling; `tests/` mirrors PHP, JavaScript, script, and acceptance responsibilities.
- `tools/`, `node_modules/`, `assets/dist/`, and `build/` are generated locally. Downloaded tools and development dependencies never enter the release ZIP.

## Component usage

All eleven components expose `make(array $args): string` and `render(array $args): void`.

```php
use Fancyn\PluginStarter\UI\Input;

Input::render(
	array(
		'name'        => 'company_name',
		'label'       => __( 'Company name', 'fancyn-plugin-starter' ),
		'value'       => $company_name,
		'description' => __( 'Displayed on reports.', 'fancyn-plugin-starter' ),
		'required'    => true,
		'error'       => $errors['company_name'] ?? '',
	)
);
```

Available components:

- Button
- Input
- Textarea
- Select
- Checkbox
- Radio
- SwitchField
- Label
- FormField
- FormSection
- ValidationMessage

Components render and escape markup only. They do not read request data, save options, perform capability checks, or enqueue dependencies.

## Settings and WordPress APIs

The Settings page demonstrates:

- Settings API registration and standard nonce handling
- Field-specific sanitization
- `manage_options` capability protection
- WordPress Color Picker
- Media uploader storing attachment IDs
- `wp_editor()`
- Translated save notices

The flow is `admin_init` registration, `register_setting()` with a sanitization callback, `settings_fields()` nonce output, `do_settings_sections()` field rendering, and `submit_button()` submission to `options.php`. WordPress performs the capability and nonce checks; the sanitizer validates each field before the option is persisted.

WordPress media/editor/color dependencies load only on pages that use them.

REST and AJAX examples are authenticated and require `manage_options`. The REST route declares a schema and permission callback. AJAX validates its nonce before sanitizing input. No public `nopriv` action is registered.

## Initialize the starter

Run the interactive initializer before installing dependencies:

```bash
npm run init
```

It asks, in order, for plugin name, plugin slug, PHP namespace, PHP prefix, and CSS prefix. It derives the text domain, option prefix, REST namespace, and main PHP filename, displays the complete JSON configuration, then requires an explicit `yes` confirmation. Answering anything else cancels with no file changes.

Validation requires a lowercase hyphenated slug, at least two valid PHP namespace segments, a lowercase snake-case PHP prefix ending in `_`, and a lowercase CSS prefix ending in `-`. Running again with identical metadata is safe; different metadata is rejected after initialization.

For automation, create a metadata file outside the project, for example `new-plugin.json`:

```json
{
  "pluginName": "Example Plugin",
  "pluginSlug": "example-plugin",
  "textDomain": "example-plugin",
  "namespace": "Vendor\\ExamplePlugin",
  "phpPrefix": "example_plugin_",
  "cssPrefix": "ep-",
  "optionPrefix": "example_plugin_",
  "restNamespace": "example-plugin/v1"
}
```

Run:

```bash
npm run rename -- --config ../new-plugin.json
```

The JSON rename mode uses the same validation and staged engine as interactive initialization.

For version 1.0, the text domain must equal the plugin slug and the option prefix must equal the PHP prefix.

## Package a release

```bash
npm run package
```

The command runs lint, all tests, the production build, build validation, and Composer-free ZIP creation. Output:

```text
build/fancyn-plugin-starter.zip
```

`.distignore` is the exclusion source. The ZIP contains compiled assets, the project-owned PHP autoloader, runtime classes, templates, languages, and README. It excludes source assets, tests, scripts, development configuration, `node_modules/`, `tools/`, `vendor/`, Composer manifests, source maps, and repository files.

The ZIP requires only the supported WordPress/PHP runtime after installation.

## Clean-site verification

Use a clean WordPress 6.5+ site with PHP 8.1+ and follow [the package smoke checklist](tests/acceptance/package-smoke.md). Install the generated ZIP through **Plugins → Add New Plugin → Upload Plugin**, activate it, explicitly enable dev mode, visit every plugin and showcase page, save settings, exercise the WordPress integrations and authorized/unauthorized HTTP paths, then disable dev mode and verify the showcase disappears. Automated tests do not replace the responsive and keyboard-only browser checks.

## Internationalization

All visible PHP strings use the `fancyn-plugin-starter` text domain. Do not concatenate translated sentence fragments. JavaScript-facing strings should be passed through WordPress localization or script translations when new dynamic messages are introduced.

Install the WordPress i18n command once, then regenerate the translation template after changing user-facing strings:

```bash
wp package install wp-cli/i18n-command
wp i18n make-pot . languages/fancyn-plugin-starter.pot --domain=fancyn-plugin-starter --exclude=node_modules,tools,assets/dist,build,.worktrees
```

Commit the generated POT file when preparing a translated release. Run the command again after renaming so the slug, text domain, and output filename match the renamed project.

## Security rules

- Treat `$_POST`, `$_GET`, REST, and AJAX data as untrusted.
- Sanitize by field type and validate before saving.
- Escape output by HTML context.
- Require a nonce for every custom mutation.
- Prefer capabilities over role names.
- Every REST route requires an explicit permission callback.
- Global functions and constants require the project prefix.

## Version 1.0 boundaries

Not included: React, shadcn/ui, public frontend components, dark mode, remote-select adapters, advanced data tables, date-range pickers, complete toast infrastructure, or a drag-and-drop form builder.

See `docs/WORDPRESS-PLUGIN-STARTER-SPEC.md` and `docs/DESIGN-RULES.md` for the authoritative product and visual requirements.
