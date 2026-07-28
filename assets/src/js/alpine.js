import Alpine from 'alpinejs';

const focusableSelector = [
  'a[href]',
  'button:not([disabled])',
  'input:not([disabled])',
  'select:not([disabled])',
  'textarea:not([disabled])',
  '[tabindex]:not([tabindex="-1"])',
].join(',');

export function createModal() {
  return {
    isOpen: false,
    trigger: null,
    show(trigger = document.activeElement) {
      this.trigger = trigger;
      this.isOpen = true;
      this.$nextTick?.(() => this.$refs?.panel?.querySelector(focusableSelector)?.focus());
    },
    close() {
      this.isOpen = false;
      this.trigger?.focus?.();
    },
    onKeydown(event) {
      if (!this.isOpen) return;
      if (event.key === 'Escape') {
        event.preventDefault?.();
        this.close();
        return;
      }
      if (event.key !== 'Tab' || !this.$refs?.panel) return;
      const focusable = [...this.$refs.panel.querySelectorAll(focusableSelector)];
      if (!focusable.length) return;
      const first = focusable[0];
      const last = focusable.at(-1);
      if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      } else if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      }
    },
  };
}

export function createTabs(ids = ['general']) {
  return {
    ids,
    active: ids[0] ?? '',
    activate(id) {
      if (this.ids.includes(id)) this.active = id;
    },
    onKeydown(event, index) {
      const keyTargets = {
        ArrowRight: (index + 1) % this.ids.length,
        ArrowLeft: (index - 1 + this.ids.length) % this.ids.length,
        Home: 0,
        End: this.ids.length - 1,
      };
      if (!(event.key in keyTargets) || !this.ids.length) return;
      event.preventDefault?.();
      this.active = this.ids[keyTargets[event.key]];
      this.$nextTick?.(() => this.$refs?.[`tab-${this.active}`]?.focus());
    },
  };
}

export function createDropdown() {
  return {
    isOpen: false,
    toggle() {
      this.isOpen = !this.isOpen;
    },
    close() {
      this.isOpen = false;
    },
    onKeydown(event) {
      if (event.key === 'Escape') this.close();
    },
  };
}

export function initAlpine(root = document, runtime = Alpine) {
  if (!root.querySelector('.fps-plugin-root')) return false;
  runtime.data('modal', createModal);
  runtime.data('tabs', createTabs);
  runtime.data('dropdown', createDropdown);
  runtime.start();
  return true;
}
