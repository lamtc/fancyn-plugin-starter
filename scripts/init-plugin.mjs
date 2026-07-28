import { createInterface } from 'node:readline/promises';
import process from 'node:process';
import { pathToFileURL } from 'node:url';

import { deriveMetadata, renameProject, validateMetadata } from './rename-plugin.mjs';

const questions = [
  { key: 'pluginName', message: 'Plugin name' },
  { key: 'pluginSlug', message: 'Plugin slug' },
  { key: 'namespace', message: 'PHP namespace' },
  { key: 'phpPrefix', message: 'PHP prefix' },
  { key: 'cssPrefix', message: 'CSS prefix' },
];

export async function initializeProject({
  root = process.cwd(),
  prompt,
  renameProject: rename = renameProject,
  write = (message) => process.stdout.write(`${message}\n`),
} = {}) {
  if (typeof prompt !== 'function') throw new Error('A prompt adapter is required.');

  const input = {};
  for (const question of questions) {
    const answer = await prompt(question);
    input[question.key] = typeof answer === 'string' ? answer.trim() : '';
  }

  const resolved = deriveMetadata(input);
  const metadata = validateMetadata(resolved);
  write(`\nResolved plugin configuration:\n${JSON.stringify(resolved, null, 2)}`);
  const confirmation = String(await prompt({ key: 'confirm', message: 'Apply these changes? (yes/no)' })).trim().toLowerCase();
  if (!['y', 'yes'].includes(confirmation)) return { confirmed: false, changed: 0 };

  const changed = await rename({ root, metadata });
  return { confirmed: true, changed: changed ? 1 : 0 };
}

async function main() {
  const readline = createInterface({ input: process.stdin, output: process.stdout });
  try {
    const prompt = ({ message }) => readline.question(`${message}: `);
    const result = await initializeProject({ prompt });
    process.stdout.write(result.confirmed ? `Initialization complete (${result.changed} project update).\n` : 'Initialization cancelled. No files were changed.\n');
  } finally {
    readline.close();
  }
}

if (process.argv[1] && pathToFileURL(process.argv[1]).href === import.meta.url) {
  main().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
  });
}
