import AdmZip from 'adm-zip';
import { mkdtemp, mkdir, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { afterEach, describe, expect, it } from 'vitest';

import { buildZip } from '../../scripts/build-zip.mjs';

const roots = [];
async function fixture() {
  const root = await mkdtemp(path.join(tmpdir(), 'fps-package-'));
  roots.push(root);
  await mkdir(path.join(root, 'assets', 'dist'), { recursive: true });
  await mkdir(path.join(root, 'assets', 'src'), { recursive: true });
  await mkdir(path.join(root, 'includes'), { recursive: true });
  await mkdir(path.join(root, 'scripts'), { recursive: true });
  await mkdir(path.join(root, 'tests'), { recursive: true });
  await writeFile(path.join(root, 'plugin.json'), JSON.stringify({ pluginSlug: 'example-plugin' }));
  await writeFile(path.join(root, 'composer.json'), JSON.stringify({ name: 'example/plugin', autoload: { 'psr-4': { 'Example\\': 'includes/' } } }));
  await writeFile(path.join(root, 'example-plugin.php'), '<?php');
  await writeFile(path.join(root, 'includes', 'Plugin.php'), '<?php namespace Example; class Plugin {}');
  await writeFile(path.join(root, 'assets', 'dist', 'admin.js'), 'export{}');
  await writeFile(path.join(root, 'assets', 'src', 'admin.js'), 'source');
  await writeFile(path.join(root, 'scripts', 'build.mjs'), 'source');
  await writeFile(path.join(root, 'tests', 'test.php'), '<?php');
  await writeFile(path.join(root, 'package.json'), '{}');
  await writeFile(path.join(root, '.gitattributes'), '* text=auto eol=lf');
  await writeFile(path.join(root, '.distignore'), ['node_modules/', '.git/', '.worktrees/', 'tests/', 'scripts/', 'assets/src/', '*.map', 'package.json', 'package-lock.json', 'composer.json', 'composer.lock', 'plugin.json', '.gitattributes', '.distignore', 'build/'].join('\n'));
  return root;
}
afterEach(async () => Promise.all(roots.splice(0).map((root) => rm(root, { recursive: true, force: true }))));

describe('buildZip', () => {
  it('packages runtime files and Composer production autoload only', async () => {
    const root = await fixture();
    const zipPath = await buildZip({ root, outputDirectory: path.join(root, 'output') });
    const entries = new AdmZip(zipPath).getEntries().map((entry) => entry.entryName.replaceAll('\\', '/'));
    expect(entries).toContain('example-plugin/example-plugin.php');
    expect(entries).toContain('example-plugin/assets/dist/admin.js');
    expect(entries).toContain('example-plugin/vendor/autoload.php');
    expect(entries.some((entry) => entry.includes('/tests/'))).toBe(false);
    expect(entries.some((entry) => entry.includes('/scripts/'))).toBe(false);
    expect(entries.some((entry) => entry.includes('/assets/src/'))).toBe(false);
    expect(entries.some((entry) => entry.endsWith('/package.json'))).toBe(false);
    expect(entries.some((entry) => entry.endsWith('/.gitattributes'))).toBe(false);
  });

  it('replaces an existing ZIP deterministically', async () => {
    const root = await fixture();
    const outputDirectory = path.join(root, 'output');
    const first = await buildZip({ root, outputDirectory });
    const second = await buildZip({ root, outputDirectory });
    expect(second).toBe(first);
    expect((await readFile(second)).byteLength).toBeGreaterThan(0);
  });
});
