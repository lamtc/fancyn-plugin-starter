# Package Smoke Test

## Automated release evidence

- `npm run lint`: PASS on 2026-07-28; ESLint, Stylelint, and PHPCS exited 0 (61 PHP files checked).
- `npm run test`: PASS on 2026-07-28; Vitest passed 43 tests in 9 files and PHPUnit passed 100 tests with 407 assertions.
- `npm run build`: PASS on 2026-07-28; Vite built the manifest, 28.74 kB CSS, and 104.11 kB JavaScript bundles.
- `npm run validate:build`: PASS on 2026-07-28; both production entries and CSS isolation validated.
- `npm run package`: PASS on 2026-07-28; repeated lint, tests, build, and validation before creating the ZIP.
- `npm audit`: PASS on 2026-07-28; 0 vulnerabilities.
- ZIP forbidden-file audit: PASS; 66 entries under one `fancyn-plugin-starter/` root, 0 forbidden paths, and no source maps.
- ZIP runtime audit: PASS; main plugin file, README, Vite manifest, production Composer autoloader, and languages directory are present.
- ZIP PHP syntax audit: PASS; `php -l` reported 0 failures across all 43 packaged PHP files.

## Clean WordPress procedure

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
| WordPress version | Pending | Live environment |
| PHP version | Pending | Live environment |
| ZIP SHA-256 | PASS | `00A88A42A6E8DCBAF07F71E031E66127D9C8CCD51FE785EA603DE6F9DC1CF4FD` |
| Install and activate | Pending | Live environment |
| Dashboard render | Pending | Live environment |
| Settings save | Pending | Live environment |
| Components showcase | Pending | Live environment |
| Keyboard accessibility | Pending | Browser backend unavailable |
| Responsive layouts | Pending | Browser backend unavailable |
| CSS isolation on other admin pages | Pending | Browser backend unavailable |
| REST/AJAX authorization | Pending | Live environment |

Do not mark this checklist complete from automated unit tests alone. Record direct live-site evidence for every row.
