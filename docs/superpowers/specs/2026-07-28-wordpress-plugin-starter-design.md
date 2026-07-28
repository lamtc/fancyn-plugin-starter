# WordPress Plugin Starter Kit Design

**Date:** 2026-07-28

**Status:** Approved for implementation planning

## 1. Objective

Build a reusable WordPress plugin starter that generates independent, distributable plugins with a namespaced PHP architecture, a PHP-rendered admin UI, scoped Tailwind CSS, Vite assets, reusable form components, selected WordPress API examples, and a validated ZIP release workflow.

Version 1.0 is an admin-only starter. It does not include a React SPA, shadcn/ui, a public-facing UI system, dark mode, remote select adapters, advanced data tables, or a drag-and-drop form builder.

## 2. Delivery Strategy

Implementation will follow vertical, runnable slices rather than completing isolated technical layers. Each milestone must leave the plugin activatable and provide a focused verification point before later integrations are added.

The sequence is:

1. Toolchain and quality gates.
2. Plugin bootstrap and lifecycle.
3. Vite, Tailwind, and asset resolution.
4. Admin shell, menu, and Settings API.
5. Shared UI rendering foundation.
6. Eleven PHP form components.
7. Alpine.js interactions.
8. Tom Select integration.
9. WordPress media, editor, and color APIs.
10. Secure REST and AJAX examples.
11. Dashboard, settings, and component demo pages.
12. Rename workflow.
13. Build validation and ZIP packaging.
14. Documentation and clean-WordPress acceptance testing.

## 3. Architecture

### 3.1 Plugin composition

The plugin entry file defines version and path constants, loads Composer, registers activation and deactivation callbacks, and starts one plugin application object. The application registers small service providers; it does not contain admin-page or business logic itself.

The starter is copied or renamed into a standalone plugin. It has no runtime dependency on another starter or UI plugin.

### 3.2 PHP boundaries

- `includes/Core`: application bootstrap, lifecycle, and service registration.
- `includes/Admin`: menus, page controllers, screen-scoped assets, and Settings API registration.
- `includes/UI`: stateless PHP renderers for the eleven required components.
- `includes/Support`: HTML attributes, view rendering, array helpers, and typed validation helpers.
- `includes/Http`: secure sample REST and AJAX endpoints.
- `templates`: presentation-only PHP templates receiving prepared variables.

UI components must not read superglobals, save options, check business permissions, or enqueue assets. Admin and HTTP services prepare and validate data before passing it to renderers or persistence APIs.

### 3.3 Front-end assets

Vite owns two logical entries: the admin JavaScript entry and admin stylesheet entry. The JavaScript entry imports focused modules for Alpine behaviors, Tom Select, textarea behavior, and WordPress API integrations.

Tailwind uses a renameable plugin-specific prefix, disables Preflight, and configures `@tailwindcss/forms` with the `class` strategy. All custom broad selectors are nested under the renameable plugin root class. Assets are enqueued only for registered plugin screens.

In development, the asset service checks the configured Vite server and uses its client and source entries when reachable. If it is unavailable, the service resolves hashed production files through `assets/dist/.vite/manifest.json`. A missing or malformed production manifest produces an administrator-facing development notice rather than a fatal error.

### 3.4 Project identity

A single `plugin.json` file is the canonical input for project identity. It contains plugin name, slug, text domain, PHP namespace, PHP prefix, CSS prefix, option prefix, and REST namespace.

Source files use explicit starter placeholders. The rename script validates identity values, replaces every supported placeholder, renames the main plugin file and root-scoped identifiers, then scans the project to fail if placeholders remain. Re-running with the same metadata is safe; running against an already customized project with different metadata fails with an actionable message.

## 4. Coding and Quality Standards

PHP follows WordPress Coding Standards throughout, including WordPress naming, documentation, escaping, sanitization, nonce, capability, and internationalization conventions. Namespaced classes use Composer PSR-4 autoloading, while unavoidable global functions and constants use the project prefix.

PHPCS uses `WordPress-Core`, `WordPress-Docs`, and `WordPress-Extra`. JavaScript and CSS use ESLint and Stylelint. PHPUnit covers PHP units and WordPress-facing service behavior with mocked WordPress functions where practical. Vitest covers JavaScript modules and Node build scripts. Build and package validation scripts provide integration-level quality gates.

## 5. UI System

### 5.1 Visual language

The Tailwind theme maps the tokens in `docs/DESIGN-RULES.md`: warm orange brand colors, neutral page and surface colors, semantic feedback colors, control/card radii, and restrained shadows. Templates consume semantic theme names instead of repeating literal colors.

The admin shell uses a light gray page background, white content cards, clear typographic hierarchy, a maximum width of 1280px, responsive one-column fallback, and visible keyboard focus. No fonts are fetched remotely.

### 5.2 Component contract

Every component supports `make(array $args): string` and `render(array $args): void`. `render()` echoes the result of `make()`. Arguments are normalized against component-specific defaults, and unknown HTML attributes are serialized through a shared allow-and-escape helper.

The required components are Button, Input, Textarea, Select, Checkbox, Radio, SwitchField, Label, FormField, FormSection, and ValidationMessage.

FormField owns the accessible relationship between label, control, description, and error. It generates stable IDs when absent, composes `aria-describedby`, and applies `aria-invalid` on error. Individual controls own only their native element and relevant state. Output is escaped according to context; the only accepted HTML fragments are documented icon or action slots filtered with an explicit `wp_kses()` allowlist.

### 5.3 Interactions

Native HTML remains the default. Alpine.js is initialized only inside plugin screens and powers the demo modal, tabs, dropdown, and conditional settings. Long Alpine objects live in JavaScript modules.

Tom Select initializes only elements carrying `data-enhanced-select`. Small lists remain native. Its CSS is scoped and themed to match native controls.

The modal restores focus, traps keyboard focus, and closes with Escape. Tabs and dropdowns implement the appropriate keyboard and ARIA behavior. Error and validation states always include text, not color alone.

## 6. WordPress Integration and Data Flow

The settings controller registers a prefixed option through the Settings API with a field-aware sanitize callback. Page access uses `manage_options`. WordPress performs the standard settings nonce check; any custom mutation supplies and verifies its own nonce.

Request flow is:

1. WordPress checks page capability and invokes the page controller.
2. The controller reads already-sanitized stored options and builds a view model.
3. The template composes UI components from the view model.
4. Settings API validates and saves submitted values.
5. WordPress redirects and the page renders a translated save notice.

The media helper stores attachment IDs and updates a preview. The editor uses `wp_editor()`. The color field initializes WordPress Color Picker only where its data attribute is present. Dependencies for each API are enqueued only on pages that demonstrate or use that API.

REST and AJAX examples are non-public. Both require capability and nonce authentication, sanitize input by field type, validate before processing, and return structured success or error payloads. The REST route includes an explicit permission callback and argument schema.

## 7. Demo Surfaces

Development mode exposes a component showcase under Tools and a settings example. Production registration is controlled by a renameable configuration constant and defaults to disabled for the component showcase.

The showcase includes all button variants and sizes, control states, native and enhanced selects, checkbox, radio, switch, validation messages, form sections, color picker, media uploader, editor, modal, tabs, and dropdown. The settings page demonstrates a real Settings API round trip. A dashboard page demonstrates the page shell, cards, responsive grid, notices, and empty-state partial.

## 8. Error Handling

- Activation checks PHP and WordPress minimum versions and fails with a localized message when unsupported.
- Asset loading skips safely and displays an admin notice when neither Vite nor a valid manifest is available.
- Component arguments fall back to safe defaults; invalid enumerated variants and sizes are normalized rather than emitted as arbitrary classes.
- Rename and package scripts stop on validation errors, print the failed condition, and leave the source project intact.
- REST and AJAX failures use WordPress error responses with stable machine-readable codes and localized messages.
- Uninstall removes only the starter's explicitly registered options and runs only after verifying `WP_UNINSTALL_PLUGIN`.

## 9. Packaging

`npm run package` runs lint, tests, production build, and build validation before staging a release. `.distignore` is the single source of exclusion rules. The packager creates `<plugin-slug>.zip`, verifies required runtime files and Composer production autoload files, and rejects development sources, tests, maps, configuration files, or dependencies prohibited by the product specification.

The ZIP must install and activate on a clean supported WordPress installation without requiring Node, Composer, or another plugin.

## 10. Verification and Acceptance

Automated verification covers:

- Bootstrap and service registration.
- Screen-restricted enqueue behavior and manifest resolution.
- Component escaping, attributes, states, and accessibility relationships.
- Settings sanitization and permission behavior.
- REST/AJAX permission, nonce, schema, and error paths.
- Alpine and Tom Select initializer targeting.
- Rename validation, replacement completeness, and idempotency.
- Manifest and ZIP contents.

Manual acceptance covers:

- Installation and activation of the generated ZIP on a clean WordPress site.
- Vite HMR and production fallback.
- All component and WordPress API demos.
- Keyboard operation and visible focus for modal, tabs, dropdown, and controls.
- Mobile overflow and responsive layout.
- Visual consistency between native select, Tom Select, WordPress editor, and color picker.
- Confirmation that plugin CSS and assets do not affect unrelated admin screens.

Completion requires every acceptance criterion in `docs/WORDPRESS-PLUGIN-STARTER-SPEC.md` and `docs/DESIGN-RULES.md` to pass.
