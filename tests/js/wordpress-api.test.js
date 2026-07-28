// @vitest-environment jsdom
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { initColorPickers, initMediaFields } from '../../assets/src/js/wordpress-api.js';

function mediaRuntime(attachment = { id: 42, url: 'https://example.test/image.jpg', alt: 'Preview' }) {
  const handlers = {};
  const frame = {
    on: vi.fn((event, callback) => { handlers[event] = callback; }),
    open: vi.fn(() => handlers.select?.()),
    state: () => ({ get: () => ({ first: () => ({ toJSON: () => attachment }) }) }),
  };
  return { runtime: { media: vi.fn(() => frame) }, frame };
}

describe('WordPress media fields', () => {
  beforeEach(() => {
    document.body.innerHTML = '<div data-media-field data-media-type="image"><input data-media-id value=""><button data-media-select>Select</button><button data-media-remove>Remove</button><img data-media-preview hidden></div>';
  });

  it('stores attachment ID and updates preview after selection', () => {
    const { runtime } = mediaRuntime();
    initMediaFields(document, runtime);
    document.querySelector('[data-media-select]').click();
    expect(document.querySelector('[data-media-id]').value).toBe('42');
    expect(document.querySelector('[data-media-preview]').src).toBe('https://example.test/image.jpg');
    expect(document.querySelector('[data-media-preview]').hidden).toBe(false);
  });

  it('clears stored attachment and reuses one frame per field', () => {
    const { runtime } = mediaRuntime();
    initMediaFields(document, runtime);
    document.querySelector('[data-media-select]').click();
    document.querySelector('[data-media-select]').click();
    expect(runtime.media).toHaveBeenCalledOnce();
    document.querySelector('[data-media-remove]').click();
    expect(document.querySelector('[data-media-id]').value).toBe('');
    expect(document.querySelector('[data-media-preview]').hidden).toBe(true);
  });

  it('is a harmless no-op when wp.media is unavailable', () => {
    expect(() => initMediaFields(document, {})).not.toThrow();
  });
});

describe('WordPress color picker', () => {
  it('initializes only marked fields once', () => {
    document.body.innerHTML = '<input data-color-picker><input>';
    const picker = vi.fn();
    const jquery = vi.fn(() => ({ wpColorPicker: picker }));
    initColorPickers(document, { jQuery: jquery });
    initColorPickers(document, { jQuery: jquery });
    expect(picker).toHaveBeenCalledOnce();
  });

  it('does nothing without the WordPress jQuery plugin', () => {
    document.body.innerHTML = '<input data-color-picker>';
    expect(() => initColorPickers(document, {})).not.toThrow();
  });
});
