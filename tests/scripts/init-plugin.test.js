import { readFile } from 'node:fs/promises';
import { describe, expect, it, vi } from 'vitest';

import { initializeProject } from '../../scripts/init-plugin.mjs';

const answersFor = (values) => {
  const remaining = [...values];
  return vi.fn(async () => remaining.shift());
};

describe('initializeProject', () => {
  it('collects five identities, shows derived metadata, and renames after confirmation', async () => {
    const prompt = answersFor(['Example Plugin', 'example-plugin', 'Vendor\\ExamplePlugin', 'example_plugin_', 'ep-', 'yes']);
    const renameProject = vi.fn(async () => true);
    const write = vi.fn();
    const result = await initializeProject({ root: 'C:/starter', prompt, renameProject, write });

    expect(renameProject).toHaveBeenCalledWith({
      root: 'C:/starter',
      metadata: {
        pluginName: 'Example Plugin', pluginSlug: 'example-plugin', textDomain: 'example-plugin', namespace: 'Vendor\\ExamplePlugin',
        phpPrefix: 'example_plugin_', cssPrefix: 'ep-', optionPrefix: 'example_plugin_', restNamespace: 'example-plugin/v1',
      },
    });
    expect(write.mock.calls.flat().join('\n')).toContain('example-plugin.php');
    expect(result).toEqual({ confirmed: true, changed: 1 });
  });

  it('cancels without changing the project', async () => {
    const renameProject = vi.fn();
    const result = await initializeProject({
      prompt: answersFor(['Example Plugin', 'example-plugin', 'Vendor\\ExamplePlugin', 'example_plugin_', 'ep-', 'no']),
      renameProject,
      write: vi.fn(),
    });
    expect(result).toEqual({ confirmed: false, changed: 0 });
    expect(renameProject).not.toHaveBeenCalled();
  });

  it.each([
    [1, '../bad', /pluginSlug/i],
    [2, 'single', /namespace/i],
    [3, 'Bad-Prefix', /phpPrefix/i],
    [4, 'BadPrefix', /cssPrefix/i],
  ])('rejects invalid answer %i before confirmation', async (index, invalid, error) => {
    const answers = ['Example Plugin', 'example-plugin', 'Vendor\\ExamplePlugin', 'example_plugin_', 'ep-', 'yes'];
    answers[index] = invalid;
    const renameProject = vi.fn();
    await expect(initializeProject({ prompt: answersFor(answers), renameProject, write: vi.fn() })).rejects.toThrow(error);
    expect(renameProject).not.toHaveBeenCalled();
  });

  it('imports only Node built-ins and the rename engine so it works before npm install', async () => {
    const source = await readFile(new URL('../../scripts/init-plugin.mjs', import.meta.url), 'utf8');
    const imports = [...source.matchAll(/from ['"]([^'"]+)['"]/g)].map((match) => match[1]);
    expect(imports.every((specifier) => specifier.startsWith('node:') || specifier === './rename-plugin.mjs')).toBe(true);
  });
});
