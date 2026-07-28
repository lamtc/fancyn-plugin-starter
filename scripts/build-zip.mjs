import { ZipArchive } from 'archiver';
import { createWriteStream } from 'node:fs';
import { cp, mkdir, readFile, readdir, rm } from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import createIgnore from 'ignore';

const FORBIDDEN_PATHS = new Set(['composer.json', 'composer.lock', 'vendor', 'tools']);

async function archiveDirectory(stage, slug, zipPath) {
  await new Promise((resolve, reject) => {
    const output = createWriteStream(zipPath);
    const archive = new ZipArchive({ zlib: { level: 9 } });
    output.on('close', resolve);
    output.on('error', reject);
    archive.on('error', reject);
    archive.pipe(output);
    archive.directory(stage, slug);
    archive.finalize();
  });
}

export async function buildZip({ root = process.cwd(), outputDirectory } = {}) {
  const projectRoot = path.resolve(root);
  const metadata = JSON.parse(await readFile(path.join(projectRoot, 'plugin.json'), 'utf8'));
  const slug = metadata.pluginSlug;
  if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) throw new Error('plugin.json contains an invalid pluginSlug.');
  const outputRoot = path.resolve(outputDirectory ?? path.join(projectRoot, 'build'));
  const patterns = await readFile(path.join(projectRoot, '.distignore'), 'utf8');
  const matcher = createIgnore().add(patterns);
  const stage = path.join(path.dirname(projectRoot), `.${path.basename(projectRoot)}-package-${Date.now()}`);
  const zipPath = path.join(outputRoot, `${slug}.zip`);

  const ignored = (relative) => {
    if (!relative) return false;
    const normalized = relative.split(path.sep).join('/');
    const topLevel = normalized.split('/')[0];
    if (FORBIDDEN_PATHS.has(topLevel)) return true;
    return matcher.ignores(normalized) || matcher.ignores(`${normalized}/`);
  };

  try {
    await rm(stage, { recursive: true, force: true });
    await cp(projectRoot, stage, {
      recursive: true,
      filter: (source) => {
        if (source === outputRoot || source.startsWith(`${outputRoot}${path.sep}`)) return false;
        return !ignored(path.relative(projectRoot, source));
      },
    });
    const stageEntries = await readdir(stage);
    const forbidden = stageEntries.find((entry) => FORBIDDEN_PATHS.has(entry));
    if (forbidden) throw new Error(`npm-only package staging contains forbidden path: ${forbidden}`);
    await readFile(path.join(stage, 'includes', 'Autoloader.php'));
    await mkdir(outputRoot, { recursive: true });
    await rm(zipPath, { force: true });
    await archiveDirectory(stage, slug, zipPath);
    return zipPath;
  } finally {
    await rm(stage, { recursive: true, force: true });
  }
}

if (process.argv[1] && pathToFileURL(process.argv[1]).href === import.meta.url) {
  buildZip().then((zipPath) => console.log(`Created ${zipPath}`)).catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
  });
}
