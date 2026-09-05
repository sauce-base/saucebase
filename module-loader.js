import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath, pathToFileURL } from 'url';

/**
 * Module Asset Loader
 *
 * Automatically discovers enabled modules and collects their lang paths,
 * Playwright configs, and other metadata for the main Vite configuration.
 *
 * @fileoverview Modules are identified by the presence of a vite.config.js file.
 * Module CSS and JS assets are imported directly in each module's app.ts entry point.
 */

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const MODULES_PATH = 'modules';

export async function loadEnabledModuleNames(baseDir) {
    const modulesDir = path.join(baseDir, MODULES_PATH);

    try {
        const entries = await fs.readdir(modulesDir, { withFileTypes: true });

        const names = [];
        for (const entry of entries) {
            if (!entry.isDirectory() || entry.name.startsWith('.')) continue;
            try {
                await fs.access(
                    path.join(modulesDir, entry.name, 'vite.config.js'),
                );
                names.push(entry.name);
            } catch {
                // no vite.config.js — not a module directory
            }
        }

        return names;
    } catch (error) {
        console.error(
            `Failed to read modules directory at ${modulesDir}: ${error.message}`,
        );
        return [];
    }
}

async function importModuleFile(filePath, moduleName, displayName) {
    try {
        await fs.access(filePath);
    } catch (error) {
        if (error?.code === 'ENOENT') {
            return null;
        }

        console.warn(
            `Module ${moduleName}: unable to access ${displayName} - ${
                error && error.message
            }`,
        );
        return null;
    }

    try {
        return await import(pathToFileURL(filePath).href);
    } catch (error) {
        console.warn(
            `Module ${moduleName}: Invalid ${displayName} - ${error.message}`,
        );
        return null;
    }
}

/**
 * Collects Playwright projects from enabled modules
 *
 * @returns {Promise<{ projects: Object[], setups: Object[] }>} Module test projects and setup projects
 */
export async function collectModulePlaywrightConfigs() {
    const projects = [];
    const setups = [];
    const modulesDir = path.join(__dirname, MODULES_PATH);
    const enabledModules = await loadEnabledModuleNames(__dirname);
    const configFile = 'playwright.config.ts';

    for (const moduleName of enabledModules) {
        const moduleTestDir = path.join(
            MODULES_PATH,
            moduleName,
            'tests',
            'e2e',
        );
        const moduleSetupNames = [];

        const config = await importModuleFile(
            path.join(modulesDir, moduleName, configFile),
            moduleName,
            configFile,
        );

        if (config?.default) {
            const rawSetups = Array.isArray(config.default)
                ? config.default
                : [config.default];

            for (const setupConfig of rawSetups) {
                if (!setupConfig || !setupConfig.name) {
                    console.warn(
                        `Module ${moduleName}: playwright.config.ts setup entry missing 'name' field — skipped`,
                    );
                    continue;
                }
                moduleSetupNames.push(setupConfig.name);
                setups.push({
                    ...setupConfig,
                    testDir: setupConfig.testDir ?? moduleTestDir,
                });
            }
        }

        projects.push({
            name: `@${moduleName}`,
            testDir: moduleTestDir,
            dependencies: ['database.setup', ...moduleSetupNames],
        });
    }

    return { projects, setups };
}

/**
 * Collects language paths from enabled modules
 *
 * @returns {Promise<string[]>} Array of module language directory paths
 *
 * @example
 * const langPaths = await collectModuleLangPaths();
 * // Returns: ['modules/auth/lang', 'modules/billing/lang', ...]
 */
export async function collectModuleLangPaths() {
    const langPaths = [];
    const modulesDir = path.join(__dirname, MODULES_PATH);
    const enabledModules = await loadEnabledModuleNames(__dirname);

    for (const moduleName of enabledModules) {
        const langPath = path.join(MODULES_PATH, moduleName, 'lang');
        const fullLangPath = path.join(modulesDir, moduleName, 'lang');

        try {
            await fs.access(fullLangPath);
            langPaths.push(langPath);
        } catch {
            // Module doesn't have a lang directory, skip it
        }
    }

    return langPaths;
}
