import { createHash, randomUUID } from 'node:crypto';
import { spawnSync } from 'node:child_process';
import { access, mkdir, mkdtemp, readFile, readdir, rename, rm, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import AdmZip from 'adm-zip';

const SHA256_PATTERN = /^[a-f0-9]{64}$/;
const VERSION_PATTERN = /^\d+(?:\.\d+){1,3}(?:[-+][0-9A-Za-z.-]+)?$/;

export function validateManifest(manifest) {
  if (!manifest || !Array.isArray(manifest.tools) || manifest.tools.length === 0) {
    throw new Error('PHP tool manifest must contain at least one tool');
  }

  const names = new Set();
  for (const tool of manifest.tools) {
    if (!tool?.name || names.has(tool.name)) throw new Error('PHP tool names must be nonempty and unique');
    names.add(tool.name);
    if (typeof tool.version !== 'string' || !VERSION_PATTERN.test(tool.version)) throw new Error(`Invalid version for ${tool.name}`);
    if (!['phar', 'archive'].includes(tool.type)) throw new Error(`Invalid artifact type for ${tool.name}`);
    if (typeof tool.target !== 'string' || !tool.target) throw new Error(`Missing target for ${tool.name}`);
    let url;
    try {
      url = new URL(tool.url);
    } catch {
      throw new Error(`Invalid HTTPS URL for ${tool.name}`);
    }
    if (url.protocol !== 'https:') throw new Error(`Tool URL must use HTTPS: ${tool.name}`);
    if (!SHA256_PATTERN.test(tool.sha256 ?? '')) throw new Error(`Invalid SHA-256 digest for ${tool.name}`);
  }
  return true;
}

export async function verifyChecksum(buffer, expectedSha256) {
  const actual = createHash('sha256').update(buffer).digest('hex');
  if (actual !== expectedSha256) throw new Error(`Checksum mismatch: expected ${expectedSha256}, received ${actual}`);
  return true;
}

export async function detectPhpVersion() {
  const result = spawnSync('php', ['-r', 'echo PHP_VERSION;'], { encoding: 'utf8', windowsHide: true });
  if (result.error || result.status !== 0 || !result.stdout.trim()) {
    throw new Error('PHP 8.1 or newer is required and must be available on PATH');
  }
  return result.stdout.trim();
}

function assertSupportedPhp(version) {
  const match = /^(\d+)\.(\d+)/.exec(version);
  if (!match || Number(match[1]) < 8 || (Number(match[1]) === 8 && Number(match[2]) < 1)) {
    throw new Error(`PHP 8.1 or newer is required; detected ${version}`);
  }
}

async function flattenSingleDirectory(directory) {
  const entries = await readdir(directory, { withFileTypes: true });
  if (entries.length !== 1 || !entries[0].isDirectory()) return;
  const nested = path.join(directory, entries[0].name);
  for (const entry of await readdir(nested)) await rename(path.join(nested, entry), path.join(directory, entry));
  await rm(nested, { recursive: true, force: true });
}

export async function extractArchive(archivePath, destination) {
  new AdmZip(archivePath).extractAllTo(destination, true);
  await flattenSingleDirectory(destination);
}

async function cacheMatches(destination, tool) {
  try {
    const complete = JSON.parse(await readFile(path.join(destination, '.complete.json'), 'utf8'));
    await access(path.join(destination, tool.type === 'phar' ? tool.target : ''));
    return complete.name === tool.name && complete.version === tool.version && complete.sha256 === tool.sha256;
  } catch {
    return false;
  }
}

async function installAtomically(staging, destination) {
  const backup = `${destination}.backup-${randomUUID()}`;
  let backedUp = false;
  try {
    try {
      await rename(destination, backup);
      backedUp = true;
    } catch (error) {
      if (error.code !== 'ENOENT') throw error;
    }
    await mkdir(path.dirname(destination), { recursive: true });
    await rename(staging, destination);
    if (backedUp) await rm(backup, { recursive: true, force: true });
  } catch (error) {
    if (backedUp) {
      await rm(destination, { recursive: true, force: true });
      await rename(backup, destination);
    }
    throw error;
  }
}

export async function setupPhpTools({
  root = process.cwd(),
  manifest,
  fetch: fetchImpl = globalThis.fetch,
  phpVersion = detectPhpVersion,
  extract = extractArchive,
  checksum = verifyChecksum,
  log = console.log,
} = {}) {
  const resolvedManifest = manifest ?? JSON.parse(await readFile(path.join(root, 'tools', 'php-tools.json'), 'utf8'));
  validateManifest(resolvedManifest);
  assertSupportedPhp(await phpVersion());
  const stagingRoot = path.join(root, 'tools', '.staging');
  await mkdir(stagingRoot, { recursive: true });

  for (const tool of resolvedManifest.tools) {
    const destination = path.join(root, 'tools', tool.name, tool.version);
    if (await cacheMatches(destination, tool)) {
      log(`Using cached ${tool.name} ${tool.version}`);
      continue;
    }

    const staging = await mkdtemp(path.join(stagingRoot, `${tool.name}-`));
    try {
      let response;
      try {
        response = await fetchImpl(tool.url);
      } catch (error) {
        throw new Error(`Failed to download ${tool.name}: ${error.message}`, { cause: error });
      }
      if (!response?.ok) throw new Error(`Failed to download ${tool.name}: HTTP ${response?.status ?? 'unknown'}`);
      const bytes = Buffer.from(await response.arrayBuffer());
      await checksum(bytes, tool.sha256);
      if (tool.type === 'phar') {
        await writeFile(path.join(staging, tool.target), bytes);
      } else {
        const archive = path.join(stagingRoot, `${tool.name}-${randomUUID()}.zip`);
        try {
          await writeFile(archive, bytes);
          await extract(archive, staging);
        } finally {
          await rm(archive, { force: true });
        }
      }
      await writeFile(path.join(staging, '.complete.json'), `${JSON.stringify({ name: tool.name, version: tool.version, sha256: tool.sha256 }, null, 2)}\n`);
      await installAtomically(staging, destination);
      log(`Installed ${tool.name} ${tool.version}`);
    } finally {
      await rm(staging, { recursive: true, force: true });
    }
  }
}

async function main() {
  await setupPhpTools();
}

if (process.argv[1] && pathToFileURL(path.resolve(process.argv[1])).href === import.meta.url) {
  main().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
  });
}
