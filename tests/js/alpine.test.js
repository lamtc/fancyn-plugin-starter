// @vitest-environment jsdom
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { createDropdown, createModal, createTabs, initAlpine } from '../../assets/src/js/alpine.js';

describe('Alpine initialization', () => {
  beforeEach(() => {
    document.body.innerHTML = '';
  });

  it('does not start Alpine outside plugin screens', () => {
    const runtime = { data: vi.fn(), start: vi.fn() };
    expect(initAlpine(document, runtime)).toBe(false);
    expect(runtime.start).not.toHaveBeenCalled();
  });

  it('registers focused factories and starts once inside the plugin root', () => {
    document.body.innerHTML = '<div class="fps-plugin-root"></div>';
    const runtime = { data: vi.fn(), start: vi.fn() };
    expect(initAlpine(document, runtime)).toBe(true);
    expect(runtime.data).toHaveBeenCalledTimes(3);
    expect(runtime.start).toHaveBeenCalledOnce();
  });
});

describe('modal behavior', () => {
  it('opens, closes on Escape, and restores trigger focus', () => {
    const trigger = document.createElement('button');
    trigger.focus = vi.fn();
    const modal = createModal();
    modal.show(trigger);
    expect(modal.isOpen).toBe(true);
    modal.onKeydown(new KeyboardEvent('keydown', { key: 'Escape' }));
    expect(modal.isOpen).toBe(false);
    expect(trigger.focus).toHaveBeenCalledOnce();
  });

  it('wraps Tab focus inside the dialog', () => {
    document.body.innerHTML = '<div><button id="first"></button><button id="last"></button></div>';
    const [first, last] = document.querySelectorAll('button');
    last.focus();
    const event = { key: 'Tab', shiftKey: false, preventDefault: vi.fn() };
    const modal = createModal();
    modal.$refs = { panel: document.querySelector('div') };
    modal.isOpen = true;
    modal.onKeydown(event);
    expect(event.preventDefault).toHaveBeenCalledOnce();
    expect(document.activeElement).toBe(first);
  });
});

describe('tabs and dropdown behavior', () => {
  it('supports arrow, Home, and End tab navigation', () => {
    const tabs = createTabs(['general', 'advanced', 'api']);
    tabs.onKeydown({ key: 'ArrowRight', preventDefault: vi.fn() }, 0);
    expect(tabs.active).toBe('advanced');
    tabs.onKeydown({ key: 'End', preventDefault: vi.fn() }, 1);
    expect(tabs.active).toBe('api');
    tabs.onKeydown({ key: 'Home', preventDefault: vi.fn() }, 2);
    expect(tabs.active).toBe('general');
  });

  it('closes dropdown with Escape and outside interaction', () => {
    const dropdown = createDropdown();
    dropdown.toggle();
    expect(dropdown.isOpen).toBe(true);
    dropdown.onKeydown({ key: 'Escape' });
    expect(dropdown.isOpen).toBe(false);
    dropdown.toggle();
    dropdown.close();
    expect(dropdown.isOpen).toBe(false);
  });
});
