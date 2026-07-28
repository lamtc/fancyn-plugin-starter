/**
 * Initializes the admin application inside the plugin root.
 *
 * @param {ParentNode} documentRoot Root used to locate the plugin application.
 * @returns {Element|null} The plugin application root when present.
 */
export function initializeAdmin(documentRoot = document) {
  const root = documentRoot.querySelector('.fps-plugin-root');
  if (!root) return null;
  initTextareas(root);
  initTomSelects(root);
  initAlpine(documentRoot);
  return root;
}

if (typeof document !== 'undefined') {
  initializeAdmin();
}
import { initAlpine } from './alpine.js';
import { initTextareas } from './textarea.js';
import { initTomSelects } from './tom-select.js';
