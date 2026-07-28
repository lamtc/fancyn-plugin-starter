// @vitest-environment jsdom
import { describe, expect, it } from 'vitest';

import { initTextareas } from '../../assets/src/js/textarea.js';

describe('textarea progressive enhancement', () => {
  it('updates character counts and auto-resizes marked controls', () => {
    document.body.innerHTML = '<div class="fps-plugin-root"><textarea data-character-counter="1" data-auto-resize="1" maxlength="10">abc</textarea><p data-character-count-output></p></div>';
    const textarea = document.querySelector('textarea');
    Object.defineProperty(textarea, 'scrollHeight', { value: 84, configurable: true });
    const cleanup = initTextareas(document.querySelector('.fps-plugin-root'));
    expect(document.querySelector('[data-character-count-output]').textContent).toBe('3 / 10');
    expect(textarea.style.height).toBe('84px');
    textarea.value = 'abcdef';
    textarea.dispatchEvent(new Event('input'));
    expect(document.querySelector('[data-character-count-output]').textContent).toBe('6 / 10');
    cleanup();
  });

  it('is a harmless no-op without targets', () => {
    document.body.innerHTML = '<div></div>';
    expect(() => initTextareas(document.body)()).not.toThrow();
  });
});
