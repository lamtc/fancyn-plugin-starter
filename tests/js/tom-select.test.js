// @vitest-environment jsdom
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { initTomSelects } from '../../assets/src/js/tom-select.js';

class FakeTomSelect {
  static instances = [];
  constructor(element, config) {
    this.element = element;
    this.config = config;
    this.destroy = vi.fn();
    this.disable = vi.fn();
    FakeTomSelect.instances.push(this);
  }
}

describe('Tom Select initializer', () => {
  beforeEach(() => {
    FakeTomSelect.instances = [];
    document.body.innerHTML = '';
  });

  it('initializes only explicitly enhanced selects', () => {
    document.body.innerHTML = '<div><select data-enhanced-select="1"></select><select></select></div>';
    initTomSelects(document, FakeTomSelect);
    expect(FakeTomSelect.instances).toHaveLength(1);
  });

  it('maps progressive-enhancement data to library configuration', () => {
    document.body.innerHTML = '<select data-enhanced-select="1" data-searchable="1" data-clearable="1" data-create="1" data-placeholder="Choose" multiple disabled></select>';
    initTomSelects(document, FakeTomSelect);
    const instance = FakeTomSelect.instances[0];
    expect(instance.config).toMatchObject({
      create: true,
      placeholder: 'Choose',
      plugins: ['remove_button', 'clear_button'],
    });
    expect(instance.config.controlInput).toBeUndefined();
    expect(instance.disable).toHaveBeenCalledOnce();
  });

  it('disables search when data-searchable is absent', () => {
    document.body.innerHTML = '<select data-enhanced-select="1"></select>';
    initTomSelects(document, FakeTomSelect);
    expect(FakeTomSelect.instances[0].config.controlInput).toBeNull();
  });

  it('is idempotent and cleanup destroys initialized instances', () => {
    document.body.innerHTML = '<select data-enhanced-select="1"></select>';
    const cleanup = initTomSelects(document, FakeTomSelect);
    initTomSelects(document, FakeTomSelect);
    expect(FakeTomSelect.instances).toHaveLength(1);
    cleanup();
    expect(FakeTomSelect.instances[0].destroy).toHaveBeenCalledOnce();
  });
});
