import { describe, expect, it } from 'vitest';

import { validateCssIsolation } from '../../scripts/validate-build.mjs';

describe('release CSS isolation', () => {
  it('accepts root-scoped selectors and keyframe steps', () => {
    expect(validateCssIsolation('.fps-plugin-root{color:red}.fps-plugin-root .fps-button:hover{color:blue}.fps-plugin-root .ts-control{padding:1rem}@keyframes spin{from{transform:none}to{transform:rotate(1turn)}}')).toBe(true);
  });

  it.each([
    ['authored element selector', 'button{color:red}'],
    ['Tailwind Preflight signature', '*,::before,::after{box-sizing:border-box}'],
    ['global WordPress selector', '.wp-admin input{border:0}'],
  ])('rejects an unscoped %s', (_label, css) => {
    expect(() => validateCssIsolation(`${css}.fps-plugin-root .fps-button{color:red}`)).toThrow(/unscoped css selector/i);
  });

  it('rejects a bundle with no prefixed utility selectors', () => {
    expect(() => validateCssIsolation('.fps-plugin-root .ts-control{padding:1rem}')).toThrow(/fps- utility/i);
  });

  it('rejects embedded source map references', () => {
    expect(() => validateCssIsolation('.fps-plugin-root .fps-button{color:red}\n/*# sourceMappingURL=admin.css.map */')).toThrow(/source map/i);
  });

  it('rejects third-party selectors unless they are rooted at the plugin boundary', () => {
    expect(() => validateCssIsolation('.ts-control{padding:1rem}.fps-plugin-root .fps-button{color:red}')).toThrow(/unscoped css selector/i);
  });
});
