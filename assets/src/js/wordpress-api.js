const mediaFrames = new WeakMap();

function applyAttachment(field, attachment) {
  const input = field.querySelector('[data-media-id]');
  const preview = field.querySelector('[data-media-preview]');
  if (input) input.value = String(attachment.id ?? '');
  if (preview) {
    preview.src = attachment.url ?? '';
    preview.alt = attachment.alt ?? '';
    preview.hidden = !attachment.url;
  }
}

export function initMediaFields(root = document, wordpress = window.wp ?? {}) {
  if (typeof wordpress.media !== 'function') return () => {};
  const cleanup = [];
  root.querySelectorAll('[data-media-field]').forEach((field) => {
    const select = field.querySelector('[data-media-select]');
    const remove = field.querySelector('[data-media-remove]');
    const onSelect = (event) => {
      event.preventDefault();
      let frame = mediaFrames.get(field);
      if (!frame) {
        frame = wordpress.media({ library: { type: field.dataset.mediaType || undefined }, multiple: false });
        frame.on('select', () => applyAttachment(field, frame.state().get('selection').first().toJSON()));
        mediaFrames.set(field, frame);
      }
      frame.open();
    };
    const onRemove = (event) => {
      event.preventDefault();
      applyAttachment(field, { id: '', url: '', alt: '' });
    };
    select?.addEventListener('click', onSelect);
    remove?.addEventListener('click', onRemove);
    cleanup.push(() => {
      select?.removeEventListener('click', onSelect);
      remove?.removeEventListener('click', onRemove);
    });
  });
  return () => cleanup.forEach((callback) => callback());
}

export function initColorPickers(root = document, runtime = window) {
  if (typeof runtime.jQuery !== 'function') return;
  root.querySelectorAll('[data-color-picker]').forEach((element) => {
    if (element.dataset.colorPickerInitialized === '1') return;
    const instance = runtime.jQuery(element);
    if (typeof instance.wpColorPicker !== 'function') return;
    instance.wpColorPicker();
    element.dataset.colorPickerInitialized = '1';
  });
}
