import tailwindcss from '@tailwindcss/vite';
import inertia from '@inertiajs/vite';
import vue from '@vitejs/plugin-vue';
import fs from 'fs';
import laravel from 'laravel-vite-plugin';
import i18n from 'laravel-vue-i18n/vite';
import path from 'path';
import Icons from 'unplugin-icons/vite';
import { defineConfig } from 'vite';
import { collectModuleLangPaths } from './module-loader.js';

function getModulePrefix(filePath) {
    const match = filePath?.match(/\/modules\/([^/]+)\//);
    return match ? match[1] : null;
}

async function createConfig() {
    const sslKeyPath = 'docker/ssl/app.key.pem';
    const sslCertPath = 'docker/ssl/app.pem';
    const hasSSL = fs.existsSync(sslKeyPath) && fs.existsSync(sslCertPath);

    const moduleLangPaths = await collectModuleLangPaths();

    const frontendJson = fs.existsSync('frontend.json')
        ? JSON.parse(fs.readFileSync('frontend.json', 'utf-8'))
        : {};

    return defineConfig({
        define: {
            __SAUCEBASE_DEV__: JSON.stringify(frontendJson.dev === true),
            __SAUCEBASE_FRAMEWORK__: JSON.stringify(frontendJson.framework ?? 'vue'),
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
                    'resources/js/app.ts',
                    'resources/css/filament/admin/theme.css',
                ],
                refresh: true,
            }),
            inertia(),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            Icons({
                compiler: 'vue3',
                autoInstall: true,
            }),
            i18n({
                additionalLangPaths: moduleLangPaths,
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
                '@': path.resolve(__dirname, 'resources/js/vue'),
                '@js': path.resolve(__dirname, 'resources/js'),
                '@css': path.resolve(__dirname, 'resources/css'),
                '@modules': path.resolve(__dirname, 'modules'),
                'ziggy-js': path.resolve(__dirname, 'vendor/tightenco/ziggy'),
            },
        },
    });
}

export default createConfig();
