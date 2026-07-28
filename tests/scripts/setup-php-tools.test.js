import { createHash } from 'node:crypto';
import { access, mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { afterEach, describe, expect, it, vi } from 'vitest';

import { setupPhpTools, validateManifest, verifyChecksum } from '../../scripts/setup-php-tools.mjs';

const roots = [];
const sha256 = (value) => createHash('sha256').update(value).digest('hex');

async function fixture() {
  const root = await mkdtemp(path.join(tmpdir(), 'fps-tools-'));
  roots.push(root);
  return root;
}

function pharTool(body = 'phpunit fixture', overrides = {}) {
  return {
    name: 'phpunit',
    version: '10.5.64',
    type: 'phar',
    target: 'phpunit.phar',
    url: 'https://example.test/phpunit.phar',
    sha256: sha256(body),
    ...overrides,
  };
}

afterEach(async () => Promise.all(roots.splice(0).map((root) => rm(root, { recursive: true, force: true }))));

describe('verifyChecksum', () => {
  it('accepts the exact digest and rejects different bytes', async () => {
    const body = Buffer.from('trusted');
    await expect(verifyChecksum(body, sha256(body))).resolves.toBe(true);
    await expect(verifyChecksum(Buffer.from('corrupt'), sha256(body))).rejects.toThrow(/checksum/i);
  });
});

describe('validateManifest', () => {
  it.each([
    [{ tools: [pharTool('x', { url: 'http://example.test/tool' })] }, /https/i],
    [{ tools: [pharTool('x', { sha256: 'invalid' })] }, /sha-256/i],
    [{ tools: [pharTool(), pharTool()] }, /unique/i],
    [{ tools: [pharTool('x', { version: '' })] }, /version/i],
  ])('rejects invalid metadata', (manifest, message) => {
    expect(() => validateManifest(manifest)).toThrow(message);
  });
});

describe('setupPhpTools', () => {
  it('verifies and atomically installs a pinned PHAR', async () => {
    const root = await fixture();
    const body = Buffer.from('phpunit fixture');
    const manifest = { schemaVersion: 1, php: '>=8.1', tools: [pharTool(body)] };

    await setupPhpTools({
      root,
      manifest,
      fetch: vi.fn(async () => new Response(body)),
      phpVersion: async () => '8.3.25',
    });

    const destination = path.join(root, 'tools', 'phpunit', '10.5.64');
    expect(await readFile(path.join(destination, 'phpunit.phar'), 'utf8')).toBe('phpunit fixture');
    expect(JSON.parse(await readFile(path.join(destination, '.complete.json'), 'utf8'))).toMatchObject({
      name: 'phpunit',
      version: '10.5.64',
      sha256: pharTool(body).sha256,
    });
  });

  it('reuses a matching cache without fetching', async () => {
    const root = await fixture();
    const body = Buffer.from('phpunit fixture');
    const manifest = { schemaVersion: 1, php: '>=8.1', tools: [pharTool(body)] };
    const fetch = vi.fn(async () => new Response(body));
    await setupPhpTools({ root, manifest, fetch, phpVersion: async () => '8.3.25' });
    await setupPhpTools({ root, manifest, fetch, phpVersion: async () => '8.3.25' });
    expect(fetch).toHaveBeenCalledOnce();
  });

  it('rejects a checksum mismatch and preserves the previous cache', async () => {
    const root = await fixture();
    const destination = path.join(root, 'tools', 'phpunit', '10.5.64');
    await mkdir(destination, { recursive: true });
    await writeFile(path.join(destination, 'phpunit.phar'), 'previous valid tool');
    await writeFile(path.join(destination, '.complete.json'), JSON.stringify({ name: 'phpunit', version: '10.5.64', sha256: sha256('previous valid tool') }));
    const manifest = { schemaVersion: 1, php: '>=8.1', tools: [pharTool('expected')] };

    await expect(setupPhpTools({
      root,
      manifest,
      fetch: vi.fn(async () => new Response('corrupt')),
      phpVersion: async () => '8.3.25',
    })).rejects.toThrow(/checksum/i);

    expect(await readFile(path.join(destination, 'phpunit.phar'), 'utf8')).toBe('previous valid tool');
  });

  it('extracts archives through the injected extractor', async () => {
    const root = await fixture();
    const body = Buffer.from('archive fixture');
    const tool = pharTool(body, { name: 'wpcs', version: '3.4.1', type: 'archive', target: 'wpcs', url: 'https://example.test/wpcs.zip' });
    const extract = vi.fn(async (_archive, destination) => writeFile(path.join(destination, 'ruleset.xml'), '<ruleset/>'));

    await setupPhpTools({ root, manifest: { tools: [tool] }, fetch: vi.fn(async () => new Response(body)), phpVersion: async () => '8.3.25', extract });

    expect(extract).toHaveBeenCalledOnce();
    await expect(access(path.join(root, 'tools', 'wpcs', '3.4.1', 'ruleset.xml'))).resolves.toBeUndefined();
  });

  it('rejects unsupported PHP before fetching', async () => {
    const root = await fixture();
    const fetch = vi.fn();
    await expect(setupPhpTools({ root, manifest: { tools: [pharTool()] }, fetch, phpVersion: async () => '8.0.30' })).rejects.toThrow(/php 8\.1/i);
    expect(fetch).not.toHaveBeenCalled();
  });

  it('identifies the tool when a network request fails', async () => {
    const root = await fixture();
    await expect(setupPhpTools({
      root,
      manifest: { tools: [pharTool()] },
      fetch: vi.fn(async () => { throw new Error('network unavailable'); }),
      phpVersion: async () => '8.3.25',
    })).rejects.toThrow(/phpunit.*network unavailable/i);
  });
});

describe('official manifest', () => {
  it('contains only pinned HTTPS artifacts with complete SHA-256 digests', async () => {
    const manifestUrl = new URL('../../tools/php-tools.json', import.meta.url);
    const manifest = JSON.parse(await readFile(manifestUrl, 'utf8'));
    expect(() => validateManifest(manifest)).not.toThrow();
    expect(manifest.tools).toHaveLength(8);
    expect(manifest.tools.every((tool) => !tool.url.includes('latest'))).toBe(true);
    expect(pathToFileURL(path.resolve('tools/php-tools.json')).protocol).toBe('file:');
  });
});
