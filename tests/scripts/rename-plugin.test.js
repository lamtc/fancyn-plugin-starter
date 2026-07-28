import { mkdtemp, mkdir, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { afterEach, describe, expect, it } from 'vitest';

import * as fs from 'node:fs/promises';
import { deriveMetadata, renameProject, validateMetadata } from '../../scripts/rename-plugin.mjs';

const temporaryRoots = [];
const metadata = {
  pluginName: 'Example Plugin',
  pluginSlug: 'example-plugin',
  textDomain: 'example-plugin',
  namespace: 'Vendor\\ExamplePlugin',
  phpPrefix: 'example_plugin_',
  cssPrefix: 'ep-',
  optionPrefix: 'example_plugin_',
  restNamespace: 'example-plugin/v1',
};
const brandedMetadata = {
  pluginName: 'Fancyn Car Booking',
  pluginSlug: 'fancyn-car-booking',
  textDomain: 'fancyn-car-booking',
  namespace: 'Fancyn\\CarBooking',
  phpPrefix: 'fcb_',
  cssPrefix: 'fcb-',
  optionPrefix: 'fcb_',
  restNamespace: 'fancyn-car-booking/v1',
};

async function fixture() {
  const root = await mkdtemp(path.join(tmpdir(), 'fps-rename-'));
  temporaryRoots.push(root);
  await mkdir(path.join(root, 'includes'), { recursive: true });
  await mkdir(path.join(root, 'assets', 'src'), { recursive: true });
  await mkdir(path.join(root, 'docs', 'superpowers'), { recursive: true });
  await mkdir(path.join(root, 'scripts'), { recursive: true });
  await mkdir(path.join(root, 'tests', 'scripts'), { recursive: true });
  await writeFile(path.join(root, 'plugin.json'), JSON.stringify({
    pluginName: 'Fancyn Plugin Starter', pluginSlug: 'fancyn-plugin-starter', textDomain: 'fancyn-plugin-starter',
    namespace: 'Fancyn\\PluginStarter', phpPrefix: 'fancyn_plugin_starter_', cssPrefix: 'fps-',
    optionPrefix: 'fancyn_plugin_starter_', restNamespace: 'fancyn-plugin-starter/v1',
  }, null, 2));
  await writeFile(path.join(root, 'fancyn-plugin-starter.php'), 'Fancyn Plugin Starter Fancyn\\PluginStarter fancyn_plugin_starter_ FANCYN_PLUGIN_STARTER fps- fancyn-plugin-starter');
  await writeFile(path.join(root, 'includes', 'Example.php'), 'namespace Fancyn\\PluginStarter;');
  await writeFile(path.join(root, 'includes', 'Autoloader.php'), "new Autoloader( 'Fancyn\\\\PluginStarter', __DIR__ );");
  await writeFile(path.join(root, 'assets', 'src', 'admin.css'), '.fps-plugin-root { color: red; }');
  await writeFile(path.join(root, 'docs', 'superpowers', 'history.md'), 'Fancyn Plugin Starter');
  await writeFile(path.join(root, 'scripts', 'rename-plugin.mjs'), "const starter = 'fancyn-plugin-starter';");
  await writeFile(path.join(root, 'scripts', 'validate-build.mjs'), "const root = '.fps-plugin-root';");
  await writeFile(path.join(root, 'tests', 'scripts', 'rename-plugin.test.js'), "const starterSlug = 'fancyn-plugin-starter';");
  await writeFile(path.join(root, 'tests', 'scripts', 'vite-build.test.js'), "expect(prefix).toBe('fps-');");
  await writeFile(path.join(root, 'phpcs.xml.dist'), '<file>fancyn-plugin-starter.php</file><element value="fancyn_plugin_starter"/><element value="Fancyn"/>');
  return root;
}

afterEach(async () => {
  await Promise.all(temporaryRoots.splice(0).map((root) => rm(root, { recursive: true, force: true })));
});

describe('renameProject', () => {
  it('derives all dependent project identities', () => {
    expect(deriveMetadata({ pluginName: 'Example Plugin', pluginSlug: 'example-plugin', namespace: 'Vendor\\ExamplePlugin', phpPrefix: 'example_plugin_', cssPrefix: 'ep-' })).toEqual({
      ...metadata,
      mainFilename: 'example-plugin.php',
    });
  });

  it('exports strict metadata validation', () => {
    expect(validateMetadata(metadata)).toEqual(metadata);
    expect(() => validateMetadata({ ...metadata, namespace: 'SingleSegment' })).toThrow(/namespace/i);
  });

  it('replaces identities across text sources and renames the entry file', async () => {
    const root = await fixture();
    await renameProject({ root, metadata });
    const entry = await readFile(path.join(root, 'example-plugin.php'), 'utf8');
    expect(entry).toContain('Example Plugin');
    expect(entry).toContain('Vendor\\ExamplePlugin');
    expect(entry).toContain('example_plugin_');
    expect(entry).toContain('EXAMPLE_PLUGIN');
    expect(await readFile(path.join(root, 'assets', 'src', 'admin.css'), 'utf8')).toContain('.ep-plugin-root');
    expect(await readFile(path.join(root, 'includes', 'Autoloader.php'), 'utf8')).toContain("'Vendor\\\\ExamplePlugin'");
    expect(await readFile(path.join(root, 'phpcs.xml.dist'), 'utf8')).toContain('example-plugin.php');
    expect(await readFile(path.join(root, 'phpcs.xml.dist'), 'utf8')).toContain('example_plugin');
    expect(await readFile(path.join(root, 'phpcs.xml.dist'), 'utf8')).toContain('Vendor');
    expect(await readFile(path.join(root, 'scripts', 'rename-plugin.mjs'), 'utf8')).toContain("'fancyn-plugin-starter'");
    expect(await readFile(path.join(root, 'scripts', 'validate-build.mjs'), 'utf8')).toContain("'.ep-plugin-root'");
    expect(await readFile(path.join(root, 'tests', 'scripts', 'rename-plugin.test.js'), 'utf8')).toContain("'fancyn-plugin-starter'");
    expect(await readFile(path.join(root, 'tests', 'scripts', 'vite-build.test.js'), 'utf8')).toContain("'ep-'");
  });

  it('allows the new identity to retain the Fancyn brand and namespace root', async () => {
    const root = await fixture();
    await renameProject({ root, metadata: brandedMetadata });

    const entry = await readFile(path.join(root, 'fancyn-car-booking.php'), 'utf8');
    expect(entry).toContain('Fancyn Car Booking');
    expect(entry).toContain('Fancyn\\CarBooking');
    expect(entry).toContain('fcb_');
    expect(entry).toContain('FCB');
    expect(await readFile(path.join(root, 'assets', 'src', 'admin.css'), 'utf8')).toContain('.fcb-plugin-root');
    expect(await readFile(path.join(root, 'phpcs.xml.dist'), 'utf8')).toContain('<element value="Fancyn"/>');
    await expect(readFile(path.join(root, 'fancyn-plugin-starter.php'))).rejects.toThrow();
  });

  it('preserves historical superpowers documents', async () => {
    const root = await fixture();
    await renameProject({ root, metadata });
    expect(await readFile(path.join(root, 'docs', 'superpowers', 'history.md'), 'utf8')).toBe('Fancyn Plugin Starter');
  });

  it('is idempotent with identical metadata', async () => {
    const root = await fixture();
    await renameProject({ root, metadata });
    await expect(renameProject({ root, metadata })).resolves.toBe(false);
  });

  it.each([
    ['pluginSlug', '../bad'],
    ['textDomain', 'different-domain'],
    ['namespace', 'not a namespace'],
    ['phpPrefix', 'Bad-Prefix'],
    ['cssPrefix', 'BadPrefix'],
    ['restNamespace', 'public'],
  ])('rejects invalid %s without changing source', async (key, value) => {
    const root = await fixture();
    const before = await readFile(path.join(root, 'fancyn-plugin-starter.php'), 'utf8');
    await expect(renameProject({ root, metadata: { ...metadata, [key]: value } })).rejects.toThrow();
    expect(await readFile(path.join(root, 'fancyn-plugin-starter.php'), 'utf8')).toBe(before);
  });

  it('refuses a different second rename', async () => {
    const root = await fixture();
    await renameProject({ root, metadata });
    await expect(renameProject({ root, metadata: { ...metadata, pluginName: 'Other Plugin' } })).rejects.toThrow(/already customized/i);
  });

  it('leaves source unchanged when an injected staged write fails', async () => {
    const root = await fixture();
    const before = await readFile(path.join(root, 'fancyn-plugin-starter.php'), 'utf8');
    const filesystem = { ...fs, writeFile: async () => { throw new Error('injected staged write failure'); } };
    await expect(renameProject({ root, metadata, filesystem })).rejects.toThrow(/injected staged write failure/i);
    expect(await readFile(path.join(root, 'fancyn-plugin-starter.php'), 'utf8')).toBe(before);
  });

  it('rolls back every file when an injected apply copy fails', async () => {
    const root = await fixture();
    const beforeEntry = await readFile(path.join(root, 'fancyn-plugin-starter.php'), 'utf8');
    const beforeClass = await readFile(path.join(root, 'includes', 'Example.php'), 'utf8');
    const beforeMetadata = await readFile(path.join(root, 'plugin.json'), 'utf8');
    let failed = false;
    const filesystem = {
      ...fs,
      cp: async (source, destination, options) => {
        const isApplyTarget = path.resolve(destination) === path.resolve(root, 'includes', 'Example.php') && String(source).includes('-rename-');
        if (isApplyTarget && !failed) {
          failed = true;
          throw new Error('injected apply copy failure');
        }
        return fs.cp(source, destination, options);
      },
    };

    await expect(renameProject({ root, metadata, filesystem })).rejects.toThrow(/injected apply copy failure/i);
    expect(await readFile(path.join(root, 'fancyn-plugin-starter.php'), 'utf8')).toBe(beforeEntry);
    expect(await readFile(path.join(root, 'includes', 'Example.php'), 'utf8')).toBe(beforeClass);
    expect(await readFile(path.join(root, 'plugin.json'), 'utf8')).toBe(beforeMetadata);
    await expect(readFile(path.join(root, 'example-plugin.php'))).rejects.toThrow();
  });

  it('rejects Composer remnants in the staged project', async () => {
    const root = await fixture();
    await writeFile(path.join(root, 'composer.json'), '{}');
    await expect(renameProject({ root, metadata })).rejects.toThrow(/composer\.json/i);
  });
});
