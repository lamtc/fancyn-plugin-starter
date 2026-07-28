function findCounter(textarea) {
  const localCounter = textarea.parentElement?.querySelector('[data-character-count-output]');
  if (localCounter) return localCounter;
  const sibling = textarea.parentElement?.nextElementSibling;
  return sibling?.matches('[data-character-count-output]') ? sibling : null;
}

function updateTextarea(textarea) {
  if (textarea.dataset.autoResize === '1') {
    textarea.style.height = 'auto';
    textarea.style.height = `${textarea.scrollHeight}px`;
  }
  if (textarea.dataset.characterCounter === '1') {
    const counter = findCounter(textarea);
    if (counter) {
      const maximum = Number.parseInt(textarea.getAttribute('maxlength') ?? '', 10);
      counter.textContent = `${textarea.value.length}${Number.isNaN(maximum) ? '' : ` / ${maximum}`}`;
    }
  }
}

export function initTextareas(root = document) {
  const textareas = [...root.querySelectorAll('textarea[data-character-counter], textarea[data-auto-resize]')];
  const listeners = textareas.map((textarea) => {
    const listener = () => updateTextarea(textarea);
    textarea.addEventListener('input', listener);
    listener();
    return [textarea, listener];
  });

  return () => listeners.forEach(([textarea, listener]) => textarea.removeEventListener('input', listener));
}
