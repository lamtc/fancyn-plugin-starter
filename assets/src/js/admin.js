/**
 * Initializes the admin application inside the plugin root.
 *
 * @param {ParentNode} documentRoot Root used to locate the plugin application.
 * @returns {Element|null} The plugin application root when present.
 */
export function initializeAdmin(documentRoot = document) {
  return documentRoot.querySelector('.fps-plugin-root');
}

if (typeof document !== 'undefined') {
  initializeAdmin();
}
