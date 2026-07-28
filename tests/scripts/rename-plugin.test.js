import { mkdtemp, mkdir, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { afterEach, describe, expect, it } from 'vitest';

import { renameProject } from '../../scripts/rename-plugin.mjs';

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

async function fixture() {
  const root = await mkdtemp(path.join(tmpdir(), 'fps-rename-'));
  temporaryRoots.push(root);
  await mkdir(path.join(root, 'includes'), { recursive: true });
  await mkdir(path.join(root, 'assets', 'src'), { recursive: true });
  await mkdir(path.join(root, 'docs', 'superpowers'), { recursive: true });
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
  return root;
}

afterEach(async () => {
  await Promise.all(temporaryRoots.splice(0).map((root) => rm(root, { recursive: true, force: true })));
});

describe('renameProject', () => {
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
});
