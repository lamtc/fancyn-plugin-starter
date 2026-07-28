# Rename Token Collision Fix Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make plugin initialization succeed when the new plugin name and PHP namespace legitimately retain the `Fancyn` brand, and document a fail-fast packaging workflow.

**Architecture:** Replace only complete project-identity tokens and the exact PHPCS namespace-root XML value; remove the unsafe global namespace-root replacement. Prove the behavior through a fixture-level regression test and a clean-clone package acceptance using the user's exact metadata.

**Tech Stack:** Node.js ES modules, Vitest, npm scripts, WordPress plugin ZIP packaging, Markdown

---

### Task 1: Reproduce the branded metadata collision with a regression test

**Files:**
- Modify: `tests/scripts/rename-plugin.test.js`
- Test: `tests/scripts/rename-plugin.test.js`

- [ ] **Step 1: Add the exact branded metadata fixture**

Add beside the existing `metadata` constant:

```js
const brandedMetadata = {
  pluginName: 'Fancyn Car Booking',
  pluginSlug: 'fancyn-car-booking',
  textDomain: 'fancyn-car-booking',
  namespace: 'Fancyn\\CarBooking',
  phpPrefix: 'fcbook_',
  cssPrefix: 'fcb-',
  optionPrefix: 'fcbook_',
  restNamespace: 'fancyn-car-booking/v1',
};
```

- [ ] **Step 2: Add the failing regression test**

Add inside `describe('renameProject', ...)`:

```js
it('allows the new identity to retain the Fancyn brand and namespace root', async () => {
  const root = await fixture();
  await renameProject({ root, metadata: brandedMetadata });

  const entry = await readFile(path.join(root, 'fancyn-car-booking.php'), 'utf8');
  expect(entry).toContain('Fancyn Car Booking');
  expect(entry).toContain('Fancyn\\CarBooking');
  expect(entry).toContain('fcbook_');
  expect(entry).toContain('FCBOOK');
  expect(await readFile(path.join(root, 'assets', 'src', 'admin.css'), 'utf8')).toContain('.fcb-plugin-root');
  expect(await readFile(path.join(root, 'phpcs.xml.dist'), 'utf8')).toContain('<element value="Fancyn"/>');
  await expect(readFile(path.join(root, 'fancyn-plugin-starter.php'))).rejects.toThrow();
});
```

- [ ] **Step 3: Run the focused test and verify RED**

Run:

```powershell
npx vitest run tests/scripts/rename-plugin.test.js
```

Expected: the new test fails with `Starter placeholders remain in:` while existing tests remain green.

- [ ] **Step 4: Commit the failing regression test**

```powershell
git add tests/scripts/rename-plugin.test.js
git commit -m "test: reproduce branded rename token collision"
```

### Task 2: Replace namespace-root tokens precisely

**Files:**
- Modify: `scripts/rename-plugin.mjs`
- Test: `tests/scripts/rename-plugin.test.js`

- [ ] **Step 1: Replace the global namespace-root pair with an exact PHPCS token**

Change the final replacement pair from:

```js
[starterNamespaceRoot, targetNamespaceRoot],
```

to:

```js
[`<element value="${starterNamespaceRoot}"/>`, `<element value="${targetNamespaceRoot}"/>`],
```

This updates the WPCS namespace rule without changing branded prose, plugin names, or an unchanged `Fancyn` namespace root.

- [ ] **Step 2: Exclude no-op pairs from replacement and placeholder validation**

Return only pairs whose source and destination differ:

```js
return [
  [starter.namespace.replaceAll('\\', '\\\\'), metadata.namespace.replaceAll('\\', '\\\\')],
  [starter.namespace, metadata.namespace],
  [starterConstant, targetConstant],
  [starter.phpPrefix, metadata.phpPrefix],
  [starterPrefix, targetPrefix],
  [starter.cssPrefix, metadata.cssPrefix],
  [starter.pluginSlug, metadata.pluginSlug],
  [starter.pluginName, metadata.pluginName],
  [`<element value="${starterNamespaceRoot}"/>`, `<element value="${targetNamespaceRoot}"/>`],
].filter(([search, replacement]) => search !== replacement);
```

The existing `replacements(starter)` placeholder scan then checks only transformations that can change the Starter identity.

- [ ] **Step 3: Run focused tests and verify GREEN**

Run:

```powershell
npx vitest run tests/scripts/rename-plugin.test.js tests/scripts/init-plugin.test.js
```

Expected: all rename and initializer tests pass, including idempotency and rollback tests.

- [ ] **Step 3a: Reject PHP prefixes that WPCS considers too short**

Add a validation assertion that `fcb_` is rejected with an `at least 4 characters` message, then update `validateMetadata()` so the portion before the trailing underscore contains at least four characters.

- [ ] **Step 4: Commit the implementation**

```powershell
git add scripts/rename-plugin.mjs
git commit -m "fix: allow branded plugin rename metadata"
```

### Task 3: Document the fail-fast initialization workflow

**Files:**
- Modify: `README.md`
- Modify: `README.en.md`

- [ ] **Step 1: Add a fail-fast command example to both READMEs**

Document this single chained command after the development setup block:

```powershell
npm run init && npm install && npm run package
```

Explain that `&&` prevents installation and packaging when initialization returns a non-zero exit code.

- [ ] **Step 2: Add the ZIP identity safety note**

In Vietnamese, state:

```markdown
Sau khi khởi tạo thành công, tên ZIP phải khớp với plugin slug mới. Không upload `fancyn-plugin-starter.zip` vào website đang chứa thư mục source Starter cùng slug, vì WordPress sẽ thay thế thư mục đó bằng nội dung ZIP production.
```

Add the equivalent English warning in `README.en.md`.

- [ ] **Step 3: Verify bilingual structure and commands**

Run:

```powershell
rg -n "npm run init && npm install && npm run package|fancyn-plugin-starter.zip" README.md README.en.md
git diff --check
```

Expected: both READMEs contain the fail-fast workflow and same-slug upload warning with no whitespace errors.

- [ ] **Step 4: Commit documentation**

```powershell
git add README.md README.en.md
git commit -m "docs: clarify fail-fast plugin packaging"
```

### Task 4: Prove the full clean-clone workflow and publish

**Files:**
- Verify: `scripts/rename-plugin.mjs`
- Verify: `tests/scripts/rename-plugin.test.js`
- Verify: `build/fancyn-car-booking.zip` in an untracked temporary clone

- [ ] **Step 1: Run the complete release gate**

```powershell
npm run lint
npm test
npm run build
npm run validate:build
npm run package
npm audit
git diff --check
```

Expected: all commands exit 0, all JavaScript/PHP tests pass, and npm reports zero known vulnerabilities.

- [ ] **Step 2: Run clean-clone initialization with the exact metadata**

Clone the feature branch into a temporary directory, pipe these six answers to `npm run init`, then run the remaining commands from inside the renamed clone:

```text
Fancyn Car Booking
fancyn-car-booking
Fancyn\CarBooking
fcbook_
fcb-
yes
```

```powershell
npm install
npm run lint
npm test
npm run package
```

Expected: initialization exits 0 and creates `fancyn-car-booking.php`; lint/tests/package exit 0.

- [ ] **Step 3: Audit the renamed ZIP**

```powershell
tar -tf build/fancyn-car-booking.zip
```

Expected: every entry is rooted under `fancyn-car-booking/`, the main PHP file is `fancyn-car-booking/fancyn-car-booking.php`, and runtime files contain no `fancyn-plugin-starter` identity.

- [ ] **Step 4: Merge the verified branch and push main**

```powershell
git push origin main
```

Expected: `origin/main` advances to the verified fix commit and both local/remote main resolve to the same SHA.
