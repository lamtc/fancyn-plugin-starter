import TomSelect from 'tom-select';

const instances = new WeakMap();

export function initTomSelects(root = document, TomSelectClass = TomSelect) {
  const initialized = [];

  root.querySelectorAll('select[data-enhanced-select]').forEach((element) => {
    if (instances.has(element)) return;
    const plugins = [];
    if (element.multiple) plugins.push('remove_button');
    if (element.dataset.clearable === '1') plugins.push('clear_button');
    const config = {
      create: element.dataset.create === '1',
      placeholder: element.dataset.placeholder || undefined,
      plugins,
    };
    if (element.dataset.searchable !== '1') config.controlInput = null;
    const instance = new TomSelectClass(element, config);
    if (element.disabled) instance.disable();
    instances.set(element, instance);
    initialized.push([element, instance]);
  });

  return () => {
    initialized.forEach(([element, instance]) => {
      instance.destroy();
      instances.delete(element);
    });
  };
}
