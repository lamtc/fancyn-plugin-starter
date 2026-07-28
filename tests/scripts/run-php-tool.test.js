import path from 'node:path';
import { describe, expect, it, vi } from 'vitest';

import { resolvePhpTool, runPhpTool } from '../../scripts/run-php-tool.mjs';

const manifest = {
  tools: [
    { name: 'phpunit', version: '10.5.64', type: 'phar', target: 'phpunit.phar' },
    { name: 'phpcs', version: '3.13.5', type: 'archive', target: 'phpcs' },
    { name: 'wpcs', version: '3.4.1', type: 'archive', target: 'wpcs' },
    { name: 'phpcsutils', version: '1.2.3', type: 'archive', target: 'phpcsutils' },
  ],
};

describe('resolvePhpTool', () => {
  it('resolves supported executables from pinned local versions', () => {
    expect(resolvePhpTool('phpunit', { root: 'C:/project', manifest })).toBe(path.resolve('C:/project/tools/phpunit/10.5.64/phpunit.phar'));
    expect(resolvePhpTool('phpcs', { root: 'C:/project', manifest })).toBe(path.resolve('C:/project/tools/phpcs/3.13.5/bin/phpcs'));
    expect(resolvePhpTool('phpcbf', { root: 'C:/project', manifest })).toBe(path.resolve('C:/project/tools/phpcs/3.13.5/bin/phpcbf'));
  });

  it('rejects unknown tools', () => {
    expect(() => resolvePhpTool('composer', { root: 'C:/project', manifest })).toThrow(/unsupported/i);
  });
});

describe('runPhpTool', () => {
  it('runs the locally provisioned PHPUnit PHAR through PHP', async () => {
    const spawn = vi.fn(() => ({ status: 0 }));
    const result = await runPhpTool('phpunit', ['--version'], { root: 'C:/project', manifest, spawn });
    expect(spawn).toHaveBeenCalledWith('php', [path.resolve('C:/project/tools/phpunit/10.5.64/phpunit.phar'), '--version'], expect.objectContaining({ stdio: 'inherit', windowsHide: true }));
    expect(result).toBe(0);
  });

  it('passes local standards as runtime installed paths for PHPCS', async () => {
    const spawn = vi.fn(() => ({ status: 0 }));
    await runPhpTool('phpcs', ['--version'], { root: 'C:/project', manifest, spawn });
    const args = spawn.mock.calls[0][1];
    expect(args).toContain('--runtime-set');
    expect(args).toContain('installed_paths');
    expect(args.join(' ')).toContain(path.resolve('C:/project/tools/wpcs/3.4.1'));
    expect(args.join(' ')).toContain(path.resolve('C:/project/tools/phpcsutils/1.2.3'));
  });

  it('forwards nonzero statuses and reports spawn failures', async () => {
    expect(await runPhpTool('phpunit', [], { root: 'C:/project', manifest, spawn: vi.fn(() => ({ status: 7 })) })).toBe(7);
    await expect(runPhpTool('phpunit', [], { root: 'C:/project', manifest, spawn: vi.fn(() => ({ error: new Error('php missing') })) })).rejects.toThrow(/php missing/i);
  });
});
