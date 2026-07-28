import { spawnSync } from 'node:child_process';
import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const SUPPORTED_TOOLS = new Set(['phpunit', 'phpcs', 'phpcbf']);
const PHPCS_STANDARD_TOOLS = [
  'wpcs',
  'phpcsutils',
  'phpcsextra',
  'phpcompatibility',
  'phpcompatibility-paragonie',
  'phpcompatibility-wp',
];

function findManifestTool(manifest, name) {
  const tool = manifest?.tools?.find((candidate) => candidate.name === name);
  if (!tool) throw new Error(`Pinned PHP tool is missing from the manifest: ${name}`);
  return tool;
}

export function resolvePhpTool(name, { root = process.cwd(), manifest } = {}) {
  if (!SUPPORTED_TOOLS.has(name)) throw new Error(`Unsupported PHP tool: ${name}`);
  const manifestName = name === 'phpcbf' ? 'phpcs' : name;
  const tool = findManifestTool(manifest, manifestName);
  const executable = manifestName === 'phpunit' ? tool.target : path.join('bin', name);
  return path.resolve(root, 'tools', manifestName, tool.version, executable);
}

function installedPaths(root, manifest) {
  return PHPCS_STANDARD_TOOLS
    .map((name) => manifest.tools.find((tool) => tool.name === name))
    .filter(Boolean)
    .map((tool) => path.resolve(root, 'tools', tool.name, tool.version))
    .join(',');
}

export async function runPhpTool(name, args = [], {
  root = process.cwd(),
  manifest,
  spawn = spawnSync,
} = {}) {
  const resolvedManifest = manifest ?? JSON.parse(await readFile(path.join(root, 'tools', 'php-tools.json'), 'utf8'));
  const executable = resolvePhpTool(name, { root, manifest: resolvedManifest });
  const toolArgs = [executable];

  if (name === 'phpcs' || name === 'phpcbf') {
    toolArgs.push('--runtime-set', 'installed_paths', installedPaths(root, resolvedManifest));
  }
  toolArgs.push(...args);

  const result = spawn('php', toolArgs, {
    cwd: root,
    env: { ...process.env, PHP_CODESNIFFER_VERBOSITY: '0' },
    stdio: 'inherit',
    windowsHide: true,
  });
  if (result.error) throw result.error;
  return result.status ?? 1;
}

async function main() {
  const [name, ...args] = process.argv.slice(2);
  if (!name) throw new Error('Usage: node scripts/run-php-tool.mjs <phpunit|phpcs|phpcbf> [...args]');
  process.exitCode = await runPhpTool(name, args);
}

if (process.argv[1] && pathToFileURL(path.resolve(process.argv[1])).href === import.meta.url) {
  main().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
  });
}
