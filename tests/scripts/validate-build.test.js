import { mkdtemp, mkdir, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { afterEach, describe, expect, it } from 'vitest';

import { validateBuild } from '../../scripts/validate-build.mjs';

const roots = [];
async function fixture() {
  const root = await mkdtemp(path.join(tmpdir(), 'fps-build-'));
  roots.push(root);
  await mkdir(path.join(root, 'assets', 'dist', '.vite'), { recursive: true });
  await mkdir(path.join(root, 'assets', 'dist', 'assets'), { recursive: true });
  await writeFile(path.join(root, 'assets', 'dist', 'assets', 'admin.js'), 'export{}');
  await writeFile(path.join(root, 'assets', 'dist', 'assets', 'admin.css'), '.fps-plugin-root .fps-button{color:red}');
  await writeFile(path.join(root, 'assets', 'dist', '.vite', 'manifest.json'), JSON.stringify({
    'assets/src/js/admin.js': { file: 'assets/admin.js', isEntry: true },
    'assets/src/css/admin.css': { file: 'assets/admin.css', isEntry: true },
  }));
  return root;
}
afterEach(async () => Promise.all(roots.splice(0).map((root) => rm(root, { recursive: true, force: true }))));

describe('validateBuild', () => {
  it('accepts a complete manifest and referenced artifacts', async () => {
    await expect(validateBuild(await fixture())).resolves.toMatchObject({ entries: 2 });
  });

  it('rejects missing referenced files', async () => {
    const root = await fixture();
    await rm(path.join(root, 'assets', 'dist', 'assets', 'admin.js'));
    await expect(validateBuild(root)).rejects.toThrow(/missing referenced file/i);
  });

  it('rejects release source maps', async () => {
    const root = await fixture();
    await writeFile(path.join(root, 'assets', 'dist', 'assets', 'admin.js.map'), '{}');
    await expect(validateBuild(root)).rejects.toThrow(/source map/i);
  });
});
