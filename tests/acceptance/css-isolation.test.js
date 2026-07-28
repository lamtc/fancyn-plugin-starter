import { describe, expect, it } from 'vitest';
import { readFile } from 'node:fs/promises';
import postcss from 'postcss';

import { validateCssIsolation } from '../../scripts/validate-build.mjs';

describe('release CSS isolation', () => {
  it('accepts root-scoped selectors and keyframe steps', () => {
    expect(validateCssIsolation('.fps-plugin-root{color:red}.fps-plugin-root .fps-button:hover{color:blue}.fps-plugin-root .ts-control{padding:1rem}@keyframes spin{from{transform:none}to{transform:rotate(1turn)}}')).toBe(true);
  });

  it('accepts scoped Tailwind runtime variables without Preflight reset declarations', () => {
    const css = '.fps-plugin-root *,.fps-plugin-root :before,.fps-plugin-root :after,.fps-plugin-root ::backdrop{--tw-ring-color:rgb(59 130 246 / .5)}.fps-plugin-root .fps-button{color:red}';
    expect(validateCssIsolation(css)).toBe(true);
  });

  it.each([
    ['authored element selector', 'button{color:red}'],
    ['Tailwind Preflight signature', '*,::before,::after{box-sizing:border-box}'],
    ['global WordPress selector', '.wp-admin input{border:0}'],
  ])('rejects an unscoped %s', (_label, css) => {
    expect(() => validateCssIsolation(`${css}.fps-plugin-root .fps-button{color:red}`)).toThrow(/unscoped css selector/i);
  });

  it('rejects a scoped Tailwind Preflight signature', () => {
    const css = '.fps-plugin-root *,.fps-plugin-root ::before,.fps-plugin-root ::after{box-sizing:border-box}.fps-plugin-root .fps-button{color:red}';
    expect(() => validateCssIsolation(css)).toThrow(/preflight/i);
  });

  it('rejects a scoped global WordPress selector', () => {
    const css = '.fps-plugin-root .wp-admin input{border:0}.fps-plugin-root .fps-button{color:red}';
    expect(() => validateCssIsolation(css)).toThrow(/wordpress selector/i);
  });

  it('rejects a bundle with no prefixed utility selectors', () => {
    expect(() => validateCssIsolation('.fps-plugin-root .ts-control{padding:1rem}')).toThrow(/fps- utility/i);
  });

  it('rejects embedded source map references', () => {
    expect(() => validateCssIsolation('.fps-plugin-root .fps-button{color:red}\n/*# sourceMappingURL=admin.css.map */')).toThrow(/source map/i);
  });

  it('rejects third-party selectors unless they are rooted at the plugin boundary', () => {
    expect(() => validateCssIsolation('.ts-control{padding:1rem}.fps-plugin-root .fps-button{color:red}')).toThrow(/unscoped css selector/i);
    expect(() => validateCssIsolation('.fps-plugin-rooted .ts-control{padding:1rem}.fps-plugin-root .fps-button{color:red}')).toThrow(/unscoped css selector/i);
  });
});

describe('third-party layout containment', () => {
  it('uses border-box sizing for scoped utility elements without enabling Preflight', async () => {
    const css = await readFile(new URL('../../assets/src/css/components.css', import.meta.url), 'utf8');
    const root = postcss.parse(css);
    const rule = root.nodes.flatMap((node) => node.nodes ?? [])
      .find((node) => node.type === 'rule' && node.selector === '.fps-plugin-root [class*="fps-"]');

    expect(rule?.nodes.some((node) => node.type === 'decl' && node.prop === 'box-sizing' && node.value === 'border-box')).toBe(true);

    const fullWidthRule = root.nodes.flatMap((node) => node.nodes ?? [])
      .find((node) => node.type === 'rule' && node.selector === '.fps-plugin-root [class~="fps-w-full"]');
    expect(fullWidthRule?.nodes.some((node) => node.type === 'decl' && node.prop === 'margin-left' && node.value === '0')).toBe(true);
    expect(fullWidthRule?.nodes.some((node) => node.type === 'decl' && node.prop === 'margin-right' && node.value === '0')).toBe(true);
  });

  it('keeps the full-width Tom Select wrapper inside its parent', async () => {
    const css = await readFile(new URL('../../assets/src/css/components.css', import.meta.url), 'utf8');
    const root = postcss.parse(css);
    const rule = root.nodes.flatMap((node) => node.nodes ?? [])
      .find((node) => node.type === 'rule' && node.selector === '.fps-plugin-root .ts-wrapper');

    expect(rule?.nodes.some((node) => node.type === 'decl' && node.prop === 'box-sizing' && node.value === 'border-box')).toBe(true);
  });
});
