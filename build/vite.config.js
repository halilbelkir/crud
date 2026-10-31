import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'node:path';
import { rmSync } from 'node:fs';

const theme  = process.env.THEME || 'default';
const root   = resolve(import.meta.dirname, '..');
const output = resolve(root, 'public/themes', theme);

const cleanChunks = {
    name: 'clean-chunks',
    buildStart()
    {
        rmSync(resolve(output, 'chunks'), { recursive: true, force: true });
    },
};

export default defineConfig({
    base: './',
    plugins: [
        tailwindcss(),
        cleanChunks,
    ],
    build: {
        outDir: output,
        chunkSizeWarningLimit: 2500,
        emptyOutDir: false,
        copyPublicDir: false,
        rollupOptions: {
            input: {
                app: resolve(root, 'resources/themes', theme, 'assets/app.js'),
                style: resolve(root, 'resources/themes', theme, 'assets/app.css'),
            },
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: 'chunks/[name]-[hash].js',
                assetFileNames: assetInfo => assetInfo.names?.[0] === 'style.css' ? 'app.css' : 'chunks/[name]-[hash][extname]',
            },
        },
    },
});
