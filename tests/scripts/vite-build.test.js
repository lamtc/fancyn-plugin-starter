import { describe, expect, it } from 'vitest';

import tailwindConfig from '../../tailwind.config.js';
import viteConfig from '../../vite.config.js';
import postcssConfig from '../../postcss.config.js';

describe('Tailwind configuration', () => {
  it('isolates generated CSS from WordPress admin', () => {
    expect(tailwindConfig.prefix).toBe('fps-');
    expect(tailwindConfig.corePlugins.preflight).toBe(false);
    expect(tailwindConfig.content).toEqual(
      expect.arrayContaining([
        './fancyn-plugin-starter.php',
        './includes/**/*.php',
        './templates/**/*.php',
        './assets/src/**/*.{js,ts,jsx,tsx}',
      ]),
    );
    expect(tailwindConfig.plugins).toHaveLength(1);
  });

  it('maps all required semantic design tokens', () => {
    const { colors, borderRadius, boxShadow } = tailwindConfig.theme.extend;

    expect(colors.surface).toEqual({
      page: '#F4F4F3',
      DEFAULT: '#FFFFFF',
      muted: '#F8F8F7',
      selected: '#FFF3EE',
    });
    expect(colors.ink).toEqual({
      DEFAULT: '#171717',
      secondary: '#5F5F5B',
      muted: '#8A8A84',
      inverse: '#FFFFFF',
    });
    expect(colors.brand.DEFAULT).toBe('#F4511E');
    expect(colors.success.DEFAULT).toBe('#16A36A');
    expect(colors.warning.DEFAULT).toBe('#D98E04');
    expect(colors.danger.DEFAULT).toBe('#D9363E');
    expect(colors.info.DEFAULT).toBe('#2563EB');
    expect(colors.line).toEqual({ DEFAULT: '#E2E2DF', strong: '#CFCFCA' });
    expect(borderRadius).toMatchObject({ control: '10px', card: '18px' });
    expect(boxShadow.card).toContain('0 8px 24px');
    expect(boxShadow.popover).toContain('0 12px 30px');
  });
});

describe('Vite configuration', () => {
  it('builds production admin entries and a manifest without source maps', () => {
    expect(viteConfig.build.outDir).toBe('assets/dist');
    expect(viteConfig.build.manifest).toBe(true);
    expect(viteConfig.build.sourcemap).toBe(false);
    expect(viteConfig.build.minify).toBe(true);
    expect(viteConfig.build.rollupOptions.input).toEqual({
      admin: 'assets/src/js/admin.js',
      styles: 'assets/src/css/admin.css',
    });
  });

  it('prefixes the final stylesheet beneath the plugin root', () => {
    const prefixPlugin = postcssConfig.plugins.at(-1);

    expect(prefixPlugin.postcssPlugin).toBe('postcss-prefix-selector');
  });
});
