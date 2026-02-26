import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath, pathToFileURL } from 'url';

/**
 * Module Asset Loader
 *
 * Automatically discovers and collects asset paths from enabled Laravel modules.
 * Integrates with the main Vite configuration to include module assets in the build process.
 *
 * @fileoverview This loader scans enabled modules and imports their vite.config.js files
 * to collect asset paths. Only modules marked as enabled in modules_statuses.json are processed.
 */

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const STATUS_FILE = 'modules_statuses.json';
const MODULES_PATH = 'modules';

async function readJsonFile(filePath) {
    try {
        const content = await fs.readFile(filePath, 'utf-8');
        return JSON.parse(content);
    } catch (error) {
        throw new Error(`Failed to read ${filePath}: ${error.message}`);
    }
}

async function loadModuleStatuses(baseDir) {
    try {
        return await readJsonFile(path.join(baseDir, STATUS_FILE));
    } catch (error) {
        console.error(error.message);
        return null;
    }
}

async function listModuleDirectories(modulesDir) {
    try {
        const entries = await fs.readdir(modulesDir, { withFileTypes: true });
        return entries
            .filter(
                (entry) => entry.isDirectory() && !entry.name.startsWith('.'),
            )
            .map((entry) => entry.name);
    } catch (error) {
        console.error(
            `Failed to read modules directory at ${modulesDir}: ${error.message}`,
        );
        return [];
    }
}

export async function loadEnabledModuleNames(baseDir) {
    const statuses = await loadModuleStatuses(baseDir);
    if (!statuses) {
        return [];
    }

    const directories = await listModuleDirectories(
        path.join(baseDir, MODULES_PATH),
    );

    return directories.filter((name) => statuses[name] === true);
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

function extractModuleAssetPaths(moduleConfig, moduleName) {
    // Support both named exports and default exports: moduleConfig may be the
    // namespace object ({ default, ... }) or the default export itself.
    const config =
        moduleConfig && moduleConfig.default
            ? moduleConfig.default
            : moduleConfig;
    const modulePaths = config && config.paths;

    if (!modulePaths) {
        return [];
    }

    if (!Array.isArray(modulePaths)) {
        console.warn(`Module ${moduleName}: 'paths' export must be an array`);
        return [];
    }

    // Use posix join so generated paths use forward slashes consistently.
    return modulePaths.map((assetPath) =>
        path.posix.join(MODULES_PATH, moduleName, 'resources', assetPath),
    );
}

/**
 * Collects asset paths from enabled modules
 *
 * Scans the modules directory for enabled modules and imports their vite.config.js
 * files to collect asset paths that should be included in the main build.
 *
 * @param {string[]} paths - Initial array of asset paths to extend
 *
 * @returns {Promise<string[]>} Array of all asset paths including discovered module assets
 *
 * @example
 * const initialPaths = ['resources/js/app.ts'];
 * const allPaths = await collectModuleAssetsPaths(initialPaths, 'modules');
 * // Returns: ['resources/js/app.ts', 'modules/Auth/resources/css/app.css', ...]
 */
export async function collectModuleAssetsPaths(paths = []) {
    const modulesDir = path.join(__dirname, MODULES_PATH);
    const enabledModules = await loadEnabledModuleNames(__dirname);
    const configFile = 'vite.config.js';

    for (const moduleName of enabledModules) {
        const moduleConfig = await importModuleFile(
            path.join(modulesDir, moduleName, configFile),
            moduleName,
            configFile,
        );

        if (!moduleConfig) {
            continue;
        }

        const moduleAssetPaths = extractModuleAssetPaths(
            moduleConfig,
            moduleName,
        );
        paths.push(...moduleAssetPaths);
    }

    return paths;
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
 * // Returns: ['modules/Auth/lang', 'modules/Settings/lang', ...]
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
