# Package Smoke Test

## Automated release evidence

- `npm run lint`: PASS on 2026-07-28; ESLint, Stylelint, and npm-provisioned PHPCS exited 0 (66 PHP files checked).
- `npm run test`: PASS on 2026-07-28; Vitest passed 84 tests in 13 files and npm-provisioned PHPUnit passed 109 tests with 466 assertions.
- `npm run build`: PASS on 2026-07-28; Vite built the manifest, 28.89 kB CSS, and 104.11 kB JavaScript bundles.
- `npm run validate:build`: PASS on 2026-07-28; both production entries and CSS isolation validated.
- `npm run package`: PASS on 2026-07-28; repeated lint, tests, build, and validation before creating the ZIP.
- `npm audit`: PASS on 2026-07-28; 0 vulnerabilities.
- ZIP forbidden-file audit: PASS; 52 entries under one `fancyn-plugin-starter/` root, no `vendor/`, `tools/`, Composer manifests, or source maps.
- ZIP runtime audit: PASS; main plugin file, README, Vite manifest, project-owned `includes/Autoloader.php`, and languages directory are present.
- ZIP PHP syntax audit: PASS; `php -l` reported 0 failures across all 43 packaged PHP files.

## Clean WordPress procedure

Before the live checks, create a clean clone with no `node_modules/`, downloaded tool cache beneath `tools/`, `vendor/`, build output, or Composer executable on `PATH`. Run `npm run init`, `npm install`, `npm run lint`, `npm test`, and `npm run package` on Windows and a Unix-like environment. Audit the renamed ZIP for one slug root, the internal autoloader, valid PHP syntax, and absence of Composer/vendor/tools/development files; record versions and SHA-256 below.

## npm-only clean-clone evidence (2026-07-28)

- Windows 11 / Node 22.22.3 / npm 10.8.2 / PHP 8.3.25: PASS with a restricted `PATH` where `composer-present=False`.
- Clean state: PASS; no `node_modules/`, `vendor/`, `assets/dist/`, or `build/`; `tools/` contained only tracked `php-tools.json` before install.
- Piped `npm run init`: PASS for Example Deployment Plugin (`example-deployment-plugin`, `Example\DeploymentPlugin`, `example_deployment_plugin_`, `edp-`) before `npm install`.
- Windows workflow: PASS; `npm install`, `npm run lint`, `npm test`, and `npm run package` all exited 0 without Composer. Result: 86 Vitest tests and 109 PHPUnit tests / 466 assertions.
- Linux 6.6.87.2 (Docker/WSL2) / Node 22.23.1 / npm 10.9.8 / PHP 8.2.32: PASS with `composer-present=false`; the same clean init/install/lint/test/package workflow exited 0.
- Windows renamed ZIP SHA-256: `091D5EC9E6AC0EEE6A0D2EF97EC2F074401A7321E26436A274B2CE66164E0F26`.
- Linux renamed ZIP SHA-256: `E089C7CD32C8065D2182B08A0B6793F0647BAEF89D408CB25B4F00F351494E8A`.
- Renamed ZIP audit: PASS; 52 entries under one `example-deployment-plugin/` root, internal autoloader present, 34 PHP files passed `php -l`, and zero Composer manifests, `vendor/`, `tools/`, tests, scripts, source assets, source maps, or Node dependencies.

1. Generate the ZIP with `npm run package` and record its SHA-256 with `Get-FileHash build/fancyn-plugin-starter.zip -Algorithm SHA256`.
2. Start a clean WordPress 6.5+ site running PHP 8.1+ with no copy of this plugin installed.
3. In **Plugins → Add New Plugin → Upload Plugin**, select `build/fancyn-plugin-starter.zip`, choose **Install Now**, and then **Activate Plugin**. Confirm activation produces no warning, error, or fatal response.
4. Before WordPress loads plugins, define `FANCYN_PLUGIN_STARTER_DEV_MODE` as `true` in the clean site's `wp-config.php`.
5. Visit **Plugin Starter → Dashboard**, **Plugin Starter → Settings**, and **Tools → Plugin UI Kit**. Confirm all pages render and the showcase is visible only with dev mode enabled.
6. Save every Settings API field and confirm values persist after reload and the translated success notice appears.
7. Exercise the native select, Tom Select, WordPress Color Picker, Media Library selection/removal, and `wp_editor()` content save.
8. As an administrator with a valid nonce, call the plugin REST route and AJAX action and confirm successful responses. Repeat each request while logged out or without the required capability, and repeat with an invalid/missing nonce where applicable; confirm REST/AJAX requests are rejected and no data changes.
9. Operate the showcase modal, tabs, dropdown, and form controls with keyboard only, including visible focus and Escape dismissal where supported.
10. Inspect an unrelated core page such as **Dashboard → Home** and confirm plugin assets are absent and WordPress styling is unchanged.
11. Repeat plugin pages at 1440px, 768px, and 375px widths and confirm no horizontal overflow or inaccessible controls.
12. Set `FANCYN_PLUGIN_STARTER_DEV_MODE` to `false` (or remove the definition), reload WordPress, and confirm **Tools → Plugin UI Kit** disappears and its URL is inaccessible while Dashboard and Settings remain available.

## Environment and results

| Check | Result | Evidence |
|---|---|---|
| WordPress version | PASS | WordPress 7.0.2 on `plugin-dev.test` |
| PHP version | PASS | PHP 8.3.25 |
| ZIP SHA-256 | PASS | `25765868727BFEB6FADB8E269C7285BF79D337DAA517E886D8EE5BF4AD47845C` |
| Install and activate | PASS | Final ZIP expanded to an isolated plugin directory and activated through `activate_plugin()` with no plugin warning or activation error |
| Dashboard render | PASS | Browser returned the plugin root, production CSS/JS, module script tag, no PHP error text, and no console error |
| Settings save | PASS | Browser submitted the Settings API form, displayed “Settings saved.”, and rendered the persisted value after redirect |
| Components showcase | PASS | Dev mode exposed all component sections; Tom Select, color picker, media dialog, editor, modal, tabs, and dropdown initialized without console errors |
| Keyboard accessibility | PASS | Modal moved focus to Close, closed with Escape, restored trigger focus; ArrowRight changed tab and panel; dropdown closed with Escape; focused input had a visible 2px ring |
| Responsive layouts | PASS | Browser checks at 1440px, 768px, and 375px reported no horizontal document overflow; editor width remained within its parent |
| CSS isolation on other admin pages | PASS | WordPress Dashboard loaded zero plugin styles/scripts and retained zero horizontal overflow |
| REST/AJAX authorization | PASS | Authorized REST and AJAX returned success; logged-out REST returned 401; AJAX without nonce returned 403 |

Do not mark this checklist complete from automated unit tests alone. Record direct live-site evidence for every row.
