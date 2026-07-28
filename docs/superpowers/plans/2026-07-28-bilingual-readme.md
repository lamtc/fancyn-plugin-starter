# Bilingual README Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make Vietnamese the repository's default README while preserving a complete English version and packaging both files.

**Architecture:** Copy the current English documentation into `README.en.md`, add reciprocal language links, and translate the default `README.md` without modifying commands or technical identifiers. Reuse the existing npm release gate and inspect the generated ZIP to verify both documents ship.

**Tech Stack:** Markdown, npm scripts, Node ZIP packaging, Git

---

### Task 1: Create the English README

**Files:**
- Create: `README.en.md`
- Modify: `README.md`

- [ ] **Step 1: Preserve the current English documentation**

Copy the complete current `README.md` content into `README.en.md` and add this language link immediately below the title:

```markdown
[Phiên bản tiếng Việt](README.md)
```

- [ ] **Step 2: Translate the default README**

Translate all prose and headings in `README.md` into Vietnamese. Add this link immediately below the title:

```markdown
[English version](README.en.md)
```

Keep every command, code block, filename, API identifier, namespace, slug, text domain, version constraint, and URL unchanged.

- [ ] **Step 3: Compare document structure**

Run:

```powershell
rg -n "^## " README.md README.en.md
rg -n "npm run init|npm install|npm run lint|npm test|npm run package" README.md README.en.md
```

Expected: both files contain the same top-level section count and both document the complete initialization and release commands.

- [ ] **Step 4: Commit the bilingual documentation**

```powershell
git add README.md README.en.md
git commit -m "docs: add Vietnamese and English readmes"
```

### Task 2: Verify and publish the documentation update

**Files:**
- Verify: `README.md`
- Verify: `README.en.md`
- Verify: `build/fancyn-plugin-starter.zip`

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

Expected: all commands exit 0, 87 Vitest tests and 109 PHPUnit tests pass, and npm reports zero known vulnerabilities.

- [ ] **Step 2: Verify both README files are packaged**

```powershell
tar -tf build/fancyn-plugin-starter.zip | Select-String 'README(\.en)?\.md$'
```

Expected: the archive lists both `fancyn-plugin-starter/README.md` and `fancyn-plugin-starter/README.en.md`.

- [ ] **Step 3: Push main**

```powershell
git push origin main
```

Expected: GitHub `main` advances to the bilingual README commit.
