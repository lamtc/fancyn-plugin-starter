import { cp, mkdir, readFile, readdir, rename, rm, unlink, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const starter = {
  pluginName: 'Fancyn Plugin Starter',
  pluginSlug: 'fancyn-plugin-starter',
  textDomain: 'fancyn-plugin-starter',
  namespace: 'Fancyn\\PluginStarter',
  phpPrefix: 'fancyn_plugin_starter_',
  cssPrefix: 'fps-',
  optionPrefix: 'fancyn_plugin_starter_',
  restNamespace: 'fancyn-plugin-starter/v1',
};
const excludedDirectories = new Set(['.git', '.worktrees', 'assets/dist', 'build', 'coverage', 'node_modules', 'vendor', 'docs/superpowers']);
const textExtensions = new Set(['.css', '.js', '.json', '.md', '.mjs', '.php', '.txt', '.xml', '.yml', '.yaml']);

function validate(metadata) {
  const keys = Object.keys(starter);
  for (const key of keys) {
    if (typeof metadata[key] !== 'string' || !metadata[key].trim()) throw new Error(`Invalid ${key}: a non-empty string is required.`);
  }
  if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(metadata.pluginSlug)) throw new Error('Invalid pluginSlug.');
  if (metadata.textDomain !== metadata.pluginSlug) throw new Error('Invalid textDomain: it must match pluginSlug.');
  if (!/^[A-Z][A-Za-z0-9]*(?:\\[A-Z][A-Za-z0-9]*)+$/.test(metadata.namespace)) throw new Error('Invalid namespace.');
  if (!/^[a-z][a-z0-9_]*_$/.test(metadata.phpPrefix)) throw new Error('Invalid phpPrefix.');
  if (!/^[a-z][a-z0-9-]*-$/.test(metadata.cssPrefix)) throw new Error('Invalid cssPrefix.');
  if (metadata.optionPrefix !== metadata.phpPrefix) throw new Error('Invalid optionPrefix: it must match phpPrefix in v1.0.');
  if (metadata.restNamespace !== `${metadata.pluginSlug}/v1`) throw new Error('Invalid restNamespace: expected <pluginSlug>/v1.');
  return Object.fromEntries(keys.map((key) => [key, metadata[key].trim()]));
}

function ignored(relativePath) {
  const normalized = relativePath.split(path.sep).join('/');
  return [...excludedDirectories].some((directory) => normalized === directory || normalized.startsWith(`${directory}/`));
}

async function files(root, relative = '') {
  const directory = path.join(root, relative);
  const entries = await readdir(directory, { withFileTypes: true });
  const found = [];
  for (const entry of entries) {
    const child = path.join(relative, entry.name);
    if (ignored(child)) continue;
    if (entry.isDirectory()) found.push(...await files(root, child));
    else if (entry.isFile() && textExtensions.has(path.extname(entry.name).toLowerCase())) found.push(child);
  }
  return found;
}

function replacements(metadata) {
  const starterConstant = starter.phpPrefix.replace(/_+$/, '').toUpperCase();
  const targetConstant = metadata.phpPrefix.replace(/_+$/, '').toUpperCase();
  return [
    [starter.namespace, metadata.namespace],
    [starterConstant, targetConstant],
    [starter.phpPrefix, metadata.phpPrefix],
    [starter.cssPrefix, metadata.cssPrefix],
    [starter.pluginSlug, metadata.pluginSlug],
    [starter.pluginName, metadata.pluginName],
  ];
}

async function customizeStage(stage, metadata) {
  const replacementPairs = replacements(metadata);
  for (const relative of await files(stage)) {
    const fullPath = path.join(stage, relative);
    let contents = await readFile(fullPath, 'utf8');
    for (const [search, replacement] of replacementPairs) contents = contents.split(search).join(replacement);
    await writeFile(fullPath, contents);
  }
  await writeFile(path.join(stage, 'plugin.json'), `${JSON.stringify(metadata, null, 2)}\n`);
  const oldEntry = path.join(stage, `${starter.pluginSlug}.php`);
  const newEntry = path.join(stage, `${metadata.pluginSlug}.php`);
  if (oldEntry !== newEntry) await rename(oldEntry, newEntry);

  const remaining = [];
  for (const relative of await files(stage)) {
    const contents = await readFile(path.join(stage, relative), 'utf8');
    if (replacements(starter).some(([token]) => contents.includes(token))) remaining.push(relative);
  }
  if (remaining.length) throw new Error(`Starter placeholders remain in: ${remaining.join(', ')}`);
}

async function applyStage(stage, root, metadata) {
  for (const relative of await files(stage)) {
    const target = path.join(root, relative);
    await mkdir(path.dirname(target), { recursive: true });
    await cp(path.join(stage, relative), target, { force: true });
  }
  if (metadata.pluginSlug !== starter.pluginSlug) {
    await unlink(path.join(root, `${starter.pluginSlug}.php`)).catch((error) => {
      if (error.code !== 'ENOENT') throw error;
    });
  }
}

export async function renameProject({ root, metadata }) {
  const projectRoot = path.resolve(root);
  const desired = validate(metadata);
  const current = JSON.parse(await readFile(path.join(projectRoot, 'plugin.json'), 'utf8'));
  const oldEntryExists = await readFile(path.join(projectRoot, `${starter.pluginSlug}.php`), 'utf8').then(() => true).catch(() => false);
  if (!oldEntryExists) {
    if (JSON.stringify(current) === JSON.stringify(desired)) return false;
    throw new Error('Project is already customized; a second rename with different metadata is not allowed.');
  }

  const stage = path.join(path.dirname(projectRoot), `.${path.basename(projectRoot)}-rename-${Date.now()}`);
  try {
    await cp(projectRoot, stage, {
      recursive: true,
      filter: (source) => {
        const relative = path.relative(projectRoot, source);
        return !relative || !ignored(relative);
      },
    });
    await customizeStage(stage, desired);
    await applyStage(stage, projectRoot, desired);
    return true;
  } finally {
    await rm(stage, { recursive: true, force: true });
  }
}

function argument(name) {
  const index = process.argv.indexOf(`--${name}`);
  return index >= 0 ? process.argv[index + 1] : undefined;
}

async function main() {
  const root = path.resolve(argument('root') ?? process.cwd());
  const configPath = argument('config');
  if (!configPath) throw new Error('Pass desired metadata with --config <path>.');
  const metadata = JSON.parse(await readFile(path.resolve(configPath), 'utf8'));
  const changed = await renameProject({ root, metadata });
  console.log(changed ? `Renamed plugin at ${root}.` : 'Plugin already uses the requested metadata.');
}

if (process.argv[1] && pathToFileURL(process.argv[1]).href === import.meta.url) {
  main().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
  });
}
