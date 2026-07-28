import { mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { afterEach, describe, expect, it } from 'vitest';

import { validateNpmOnlySource } from '../../scripts/validate-build.mjs';

const roots = [];
const projectRoot = path.resolve(import.meta.dirname, '../..');

afterEach(async () => Promise.all(roots.splice(0).map((root) => rm(root, { recursive: true, force: true }))));

describe('npm-only project contract', () => {
  it('contains no Composer manifests or runtime vendor autoload', async () => {
    await expect(validateNpmOnlySource(projectRoot)).resolves.toBe(true);
  });

  it('uses npm-only canonical gates and PHP runners', async () => {
    const pkg = JSON.parse(await readFile(path.join(projectRoot, 'package.json'), 'utf8'));
    expect(pkg.scripts.lint).toContain('npm run lint:php');
    expect(pkg.scripts.test).toContain('npm run test:php');
    expect(pkg.scripts.package).toMatch(/npm run lint.*npm run test.*npm run build.*npm run validate:build/s);
    expect(JSON.stringify(pkg.scripts)).not.toMatch(/composer/i);
  });

  it('rejects the exact forbidden path in an npm-only source tree', async () => {
    const root = await mkdtemp(path.join(tmpdir(), 'fps-npm-only-'));
    roots.push(root);
    await writeFile(path.join(root, 'composer.json'), '{}');
    await expect(validateNpmOnlySource(root)).rejects.toThrow(/composer\.json.*npm-only/i);
  });

  it('keeps init dependency-free', async () => {
    const source = await readFile(path.join(projectRoot, 'scripts', 'init-plugin.mjs'), 'utf8');
    const imports = [...source.matchAll(/from ['"]([^'"]+)['"]/g)].map((match) => match[1]);
    expect(imports.every((specifier) => specifier.startsWith('node:') || specifier === './rename-plugin.mjs')).toBe(true);
  });
});
