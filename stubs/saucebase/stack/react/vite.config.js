import tailwindcss from '@tailwindcss/vite';
import inertia from '@inertiajs/vite';
import react from '@vitejs/plugin-react';
import fs from 'fs';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import Icons from 'unplugin-icons/vite';
import { defineConfig } from 'vite';

function getModulePrefix(filePath) {
    const match = filePath?.match(/\/modules\/([^/]+)\//);
    return match ? match[1] : null;
}

function createConfig() {
    const sslKeyPath = 'docker/ssl/app.key.pem';
    const sslCertPath = 'docker/ssl/app.pem';
    const hasSSL = fs.existsSync(sslKeyPath) && fs.existsSync(sslCertPath);

    const frontendJson = fs.existsSync('frontend.json')
        ? JSON.parse(fs.readFileSync('frontend.json', 'utf-8'))
        : {};

    return defineConfig({
        define: {
            __SAUCEBASE_DEV__: JSON.stringify(frontendJson.dev === true),
            __SAUCEBASE_FRAMEWORK__: JSON.stringify(frontendJson.framework ?? 'react'),
        },
        server: hasSSL
            ? {
                  https: {
                      key: fs.readFileSync(sslKeyPath),
                      cert: fs.readFileSync(sslCertPath),
                  },
              }
            : {},
        plugins: [
            tailwindcss(),
            laravel({
                input: [
                    'resources/js/app.tsx',
                    'resources/css/filament/admin/theme.css',
                ],
                refresh: true,
            }),
            inertia(),
            react(),
            Icons({
                compiler: 'jsx',
                jsx: 'react',
                autoInstall: true,
            }),
        ],
        build: {
            rollupOptions: {
                output: {
                    chunkFileNames: (chunkInfo) => {
                        const ids = chunkInfo.facadeModuleId
                            ? [chunkInfo.facadeModuleId]
                            : [...chunkInfo.moduleIds];
                        const prefix = ids.reduce((found, id) => found ?? getModulePrefix(id), null);
                        return prefix
                            ? `assets/${prefix}/[name]-[hash].js`
                            : 'assets/[name]-[hash].js';
                    },
                    assetFileNames: (assetInfo) => {
                        const source = assetInfo.originalFileNames?.[0] ?? assetInfo.name ?? '';
                        const prefix = getModulePrefix(source);
                        return prefix
                            ? `assets/${prefix}/[name]-[hash][extname]`
                            : 'assets/[name]-[hash][extname]';
                    },
                },
            },
        },
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js/react'),
                '@js': path.resolve(__dirname, 'resources/js'),
                '@css': path.resolve(__dirname, 'resources/css'),
                '@modules': path.resolve(__dirname, 'modules'),
                'ziggy-js': path.resolve(__dirname, 'vendor/tightenco/ziggy'),
            },
        },
    });
}

export default createConfig();
