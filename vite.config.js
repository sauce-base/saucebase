import inertia from '@inertiajs/vite';
import vue from '@vitejs/plugin-vue';
import fs from 'fs';
import laravel from 'laravel-vite-plugin';
import i18n from 'laravel-vue-i18n/vite';
import path from 'path';
import Icons from 'unplugin-icons/vite';
import { defineConfig } from 'vite';
import { collectModuleLangPaths } from './module-loader.js';

async function createConfig() {
    const sslKeyPath = 'docker/ssl/app.key.pem';
    const sslCertPath = 'docker/ssl/app.pem';
    const hasSSL = fs.existsSync(sslKeyPath) && fs.existsSync(sslCertPath);

    // Collect module language paths
    const moduleLangPaths = await collectModuleLangPaths();

    return defineConfig({
        server: hasSSL
            ? {
                  https: {
                      key: fs.readFileSync(sslKeyPath),
                      cert: fs.readFileSync(sslCertPath),
                  },
              }
            : {},
        plugins: [
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
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
                '@modules': path.resolve(__dirname, 'modules'),
                'ziggy-js': path.resolve(__dirname, 'vendor/tightenco/ziggy'),
            },
        },
    });
}

export default createConfig();
