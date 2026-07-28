import { defineConfig } from 'vite';

export default defineConfig({
  test: {
    include: ['tests/**/*.test.js'],
  },
  server: {
    host: '127.0.0.1',
    port: 5173,
    strictPort: true,
    cors: true,
  },
  build: {
    outDir: 'assets/dist',
    emptyOutDir: true,
    manifest: true,
    sourcemap: false,
    minify: true,
    rollupOptions: {
      input: {
        admin: 'assets/src/js/admin.js',
        styles: 'assets/src/css/admin.css',
      },
    },
  },
});
