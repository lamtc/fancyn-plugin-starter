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
const defaultFilesystem = { cp, mkdir, readFile, readdir, rename, rm, unlink, writeFile };

export function deriveMetadata(input) {
  return {
    pluginName: input.pluginName,
    pluginSlug: input.pluginSlug,
    textDomain: input.pluginSlug,
    namespace: input.namespace,
    phpPrefix: input.phpPrefix,
    cssPrefix: input.cssPrefix,
    optionPrefix: input.phpPrefix,
    restNamespace: `${input.pluginSlug}/v1`,
    mainFilename: `${input.pluginSlug}.php`,
  };
}

export function validateMetadata(metadata) {
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

async function files(root, relative = '', filesystem = defaultFilesystem) {
  const directory = path.join(root, relative);
  const entries = await filesystem.readdir(directory, { withFileTypes: true });
  const found = [];
  for (const entry of entries) {
    const child = path.join(relative, entry.name);
    if (ignored(child)) continue;
    if (entry.isDirectory()) found.push(...await files(root, child, filesystem));
    else if (entry.isFile() && textExtensions.has(path.extname(entry.name).toLowerCase())) found.push(child);
  }
  return found;
}

function replacements(metadata) {
  const starterConstant = starter.phpPrefix.replace(/_+$/, '').toUpperCase();
  const targetConstant = metadata.phpPrefix.replace(/_+$/, '').toUpperCase();
  return [
    [starter.namespace.replaceAll('\\', '\\\\'), metadata.namespace.replaceAll('\\', '\\\\')],
    [starter.namespace, metadata.namespace],
    [starterConstant, targetConstant],
    [starter.phpPrefix, metadata.phpPrefix],
    [starter.cssPrefix, metadata.cssPrefix],
    [starter.pluginSlug, metadata.pluginSlug],
    [starter.pluginName, metadata.pluginName],
  ];
}

async function customizeStage(stage, metadata, filesystem) {
  for (const forbidden of ['composer.json', 'composer.lock', path.join('vendor', 'autoload.php')]) {
    const exists = await filesystem.readFile(path.join(stage, forbidden)).then(() => true).catch(() => false);
    if (exists) throw new Error(`npm-only project contains forbidden Composer remnant: ${forbidden.split(path.sep).join('/')}`);
  }
  const replacementPairs = replacements(metadata);
  for (const relative of await files(stage, '', filesystem)) {
    const fullPath = path.join(stage, relative);
    let contents = await filesystem.readFile(fullPath, 'utf8');
    for (const [search, replacement] of replacementPairs) contents = contents.split(search).join(replacement);
    await filesystem.writeFile(fullPath, contents);
  }
  await filesystem.writeFile(path.join(stage, 'plugin.json'), `${JSON.stringify(metadata, null, 2)}\n`);
  const oldEntry = path.join(stage, `${starter.pluginSlug}.php`);
  const newEntry = path.join(stage, `${metadata.pluginSlug}.php`);
  if (oldEntry !== newEntry) await filesystem.rename(oldEntry, newEntry);

  const remaining = [];
  for (const relative of await files(stage, '', filesystem)) {
    const contents = await filesystem.readFile(path.join(stage, relative), 'utf8');
    if (replacements(starter).some(([token]) => contents.includes(token))) remaining.push(relative);
  }
  if (remaining.length) throw new Error(`Starter placeholders remain in: ${remaining.join(', ')}`);
}

async function applyStage(stage, root, metadata, filesystem) {
  for (const relative of await files(stage, '', filesystem)) {
    const target = path.join(root, relative);
    await filesystem.mkdir(path.dirname(target), { recursive: true });
    await filesystem.cp(path.join(stage, relative), target, { force: true });
  }
  if (metadata.pluginSlug !== starter.pluginSlug) {
    await filesystem.unlink(path.join(root, `${starter.pluginSlug}.php`)).catch((error) => {
      if (error.code !== 'ENOENT') throw error;
    });
  }
}

export async function renameProject({ root, metadata, filesystem = defaultFilesystem }) {
  const projectRoot = path.resolve(root);
  const desired = validateMetadata(metadata);
  const current = JSON.parse(await filesystem.readFile(path.join(projectRoot, 'plugin.json'), 'utf8'));
  const oldEntryExists = await filesystem.readFile(path.join(projectRoot, `${starter.pluginSlug}.php`), 'utf8').then(() => true).catch(() => false);
  if (!oldEntryExists) {
    if (JSON.stringify(current) === JSON.stringify(desired)) return false;
    throw new Error('Project is already customized; a second rename with different metadata is not allowed.');
  }

  const stage = path.join(path.dirname(projectRoot), `.${path.basename(projectRoot)}-rename-${Date.now()}`);
  try {
    await filesystem.cp(projectRoot, stage, {
      recursive: true,
      filter: (source) => {
        const relative = path.relative(projectRoot, source);
        return !relative || !ignored(relative);
      },
    });
    await customizeStage(stage, desired, filesystem);
    await applyStage(stage, projectRoot, desired, filesystem);
    return true;
  } finally {
    await filesystem.rm(stage, { recursive: true, force: true });
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
