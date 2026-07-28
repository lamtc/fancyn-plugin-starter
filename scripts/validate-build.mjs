import { access, readdir, readFile } from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

async function findMaps(directory) {
  const maps = [];
  for (const entry of await readdir(directory, { withFileTypes: true })) {
    const target = path.join(directory, entry.name);
    if (entry.isDirectory()) maps.push(...await findMaps(target));
    else if (entry.isFile() && entry.name.endsWith('.map')) maps.push(target);
  }
  return maps;
}

export async function validateBuild(root = process.cwd()) {
  const projectRoot = path.resolve(root);
  const distribution = path.join(projectRoot, 'assets', 'dist');
  const manifestPath = path.join(distribution, '.vite', 'manifest.json');
  let manifest;
  try {
    manifest = JSON.parse(await readFile(manifestPath, 'utf8'));
  } catch (error) {
    throw new Error(`Production manifest is missing or invalid: ${error.message}`);
  }

  const required = ['assets/src/js/admin.js', 'assets/src/css/admin.css'];
  const failures = [];
  for (const source of required) {
    const file = manifest[source]?.file;
    if (typeof file !== 'string' || !file) {
      failures.push(`Missing manifest entry: ${source}`);
      continue;
    }
    await access(path.join(distribution, file)).catch(() => failures.push(`Missing referenced file: ${file}`));
  }
  const maps = await findMaps(distribution);
  if (maps.length) failures.push(`Release source map found: ${maps.map((file) => path.relative(projectRoot, file)).join(', ')}`);
  if (failures.length) throw new Error(failures.join('\n'));
  return { entries: required.length, manifestPath };
}

if (process.argv[1] && pathToFileURL(process.argv[1]).href === import.meta.url) {
  validateBuild().then((result) => console.log(`Validated ${result.entries} production entries.`)).catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
  });
}
