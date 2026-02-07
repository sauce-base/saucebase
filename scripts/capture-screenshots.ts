#!/usr/bin/env tsx

import {
    chromium,
    type Browser,
    type BrowserContext,
    type Page,
} from '@playwright/test';
import fs from 'fs';
import path from 'path';

// Screenshot configuration
interface ScreenshotConfig {
    name: string;
    route: string;
    theme: 'light' | 'dark';
    auth: boolean;
}

const screenshots: ScreenshotConfig[] = [
    // Public pages (no authentication required)
    { name: 'home-light', route: '/', theme: 'light', auth: false },
    { name: 'home-dark', route: '/', theme: 'dark', auth: false },
    { name: 'login-light', route: '/auth/login', theme: 'light', auth: false },
    { name: 'login-dark', route: '/auth/login', theme: 'dark', auth: false },
    {
        name: 'register-light',
        route: '/auth/register',
        theme: 'light',
        auth: false,
    },
    {
        name: 'register-dark',
        route: '/auth/register',
        theme: 'dark',
        auth: false,
    },

    // Protected pages (authentication required)
    {
        name: 'dashboard-light',
        route: '/dashboard',
        theme: 'light',
        auth: true,
    },
    { name: 'dashboard-dark', route: '/dashboard', theme: 'dark', auth: true },
    { name: 'settings-light', route: '/settings', theme: 'light', auth: true },
    { name: 'settings-dark', route: '/settings', theme: 'dark', auth: true },
    {
        name: 'profile-dark',
        route: '/settings/profile',
        theme: 'dark',
        auth: true,
    },
    {
        name: 'profile-light',
        route: '/settings/profile',
        theme: 'light',
        auth: true,
    },
    {
        name: 'profile-edit-dark',
        route: '/settings/profile/edit',
        theme: 'dark',
        auth: true,
    },
    {
        name: 'profile-edit-light',
        route: '/settings/profile/edit',
        theme: 'light',
        auth: true,
    },

    // Admin panel (authentication required)

    {
        name: 'admin-dashboard-light',
        route: '/admin',
        theme: 'light',
        auth: true,
    },
    {
        name: 'admin-dashboard-dark',
        route: '/admin',
        theme: 'dark',
        auth: true,
    },

    // Users
    {
        name: 'admin-users-light',
        route: '/admin/users',
        theme: 'light',
        auth: true,
    },
    {
        name: 'admin-users-dark',
        route: '/admin/users',
        theme: 'dark',
        auth: true,
    },

    // Products
    {
        name: 'admin-products-light',
        route: '/admin/products',
        theme: 'light',
        auth: true,
    },
    {
        name: 'admin-products-dark',
        route: '/admin/products',
        theme: 'dark',
        auth: true,
    },
];

// Test user credentials (from modules/Auth/tests/e2e/fixtures/users.ts)
const TEST_USER = {
    email: 'chef@saucebase.dev',
    password: 'secretsauce',
};

// Helper: Set theme via localStorage before page navigation
async function setTheme(
    page: Page,
    theme: 'light' | 'dark',
    route: string,
): Promise<void> {
    if (isFilamentRoute(route)) {
        // Filament reads from localStorage key 'theme'
        await page.addInitScript((selectedTheme) => {
            localStorage.setItem('theme', selectedTheme);
        }, theme);
    } else {
        // Vue frontend reads from localStorage key 'vueuse-color-scheme'
        await page.addInitScript((selectedTheme) => {
            localStorage.setItem('vueuse-color-scheme', selectedTheme);
        }, theme);
    }
}

// Helper: Authenticate user by performing login
async function authenticateUser(page: Page): Promise<void> {
    await page.goto('/auth/login');

    // Wait for login page to load
    await page.waitForSelector('[data-testid="email"]', { timeout: 10000 });

    // Fill login form
    await page.getByTestId('email').fill(TEST_USER.email);
    await page.getByTestId('password').fill(TEST_USER.password);

    // Submit form
    await page.getByTestId('login-button').click();

    // Wait for redirect to dashboard
    await page.waitForURL('/dashboard', { timeout: 10000 });
}

// Helper: Check if route is a Filament admin panel page
function isFilamentRoute(route: string): boolean {
    return route.startsWith('/admin');
}

// Helper: Wait for Filament (Livewire) page to be ready
async function waitForFilamentReady(page: Page): Promise<void> {
    await page.waitForFunction(
        () => {
            // Livewire components are initialized when wire:id attributes exist
            const livewireEl = document.querySelector('[wire\\:id]');
            if (!livewireEl) return false;

            // Filament renders main content inside a Livewire component
            // Check that the page body has meaningful content
            const mainContent =
                document.querySelector('.fi-page') ||
                document.querySelector('.fi-dashboard') ||
                document.querySelector('[wire\\:id]');
            return !!mainContent;
        },
        { timeout: 15000 },
    );
}

// Helper: Wait for Inertia page to be ready
async function waitForInertiaReady(page: Page): Promise<void> {
    await page.waitForFunction(
        () => {
            const pageEl = document.querySelector('[data-page]');
            if (!pageEl) return false;

            const pageData = pageEl.getAttribute('data-page');
            return pageData && pageData.length > 0;
        },
        { timeout: 10000 },
    );
}

// Helper: Wait for page to be fully ready for screenshot
async function waitForPageReady(page: Page, route: string): Promise<void> {
    // Wait for network to be idle
    await page.waitForLoadState('networkidle');

    // Wait for the appropriate framework to hydrate
    if (isFilamentRoute(route)) {
        await waitForFilamentReady(page);
    } else {
        await waitForInertiaReady(page);
    }

    // Wait for all images to load
    await page.evaluate(() => {
        return Promise.all(
            Array.from(document.images)
                .filter((img) => !img.complete)
                .map(
                    (img) =>
                        new Promise((resolve) => {
                            img.onload = img.onerror = resolve;
                        }),
                ),
        );
    });

    // Small delay for animations to complete
    await page.waitForTimeout(500);
}

// Helper: Capture screenshot and save to file
async function captureScreenshot(
    page: Page,
    filename: string,
    outputDir: string,
): Promise<void> {
    const outputPath = path.join(outputDir, filename);

    await page.screenshot({
        path: outputPath,
        fullPage: false, // Capture viewport only
    });
}

// Main: Capture a single screenshot
async function capturePageScreenshot(
    browser: Browser,
    config: ScreenshotConfig,
    baseURL: string,
    outputDir: string,
): Promise<void> {
    // Create new browser context for isolation
    const context: BrowserContext = await browser.newContext({
        baseURL,
        viewport: { width: 1280, height: 900 },
        ignoreHTTPSErrors: true, // For self-signed dev certs
    });

    const page: Page = await context.newPage();

    try {
        // Set theme before navigation
        await setTheme(page, config.theme, config.route);

        // Authenticate if required
        if (config.auth) {
            await authenticateUser(page);
        }

        // Navigate to target route
        await page.goto(config.route);

        // Wait for page to be fully ready
        await waitForPageReady(page, config.route);

        // Capture screenshot
        const filename = `${config.name}.png`;
        await captureScreenshot(page, filename, outputDir);

        console.log(`  ✓ ${filename}`);
    } catch (error) {
        console.error(
            `  ✗ ${config.name}:`,
            error instanceof Error ? error.message : error,
        );
        throw error;
    } finally {
        await context.close();
    }
}

// Main function
async function main(): Promise<void> {
    // Parse CLI arguments
    const args = process.argv.slice(2);
    const onlyFlag = args.find((arg) => arg.startsWith('--only='));
    const filter = onlyFlag ? onlyFlag.split('=')[1] : null;

    // Load environment variables
    const baseURL = process.env.APP_URL || 'https://localhost';
    const outputDir = path.join(process.cwd(), 'public/images/screenshots');

    // Ensure output directory exists
    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
    }

    // Filter screenshots if --only flag provided
    const screenshotsToCapture = filter
        ? screenshots.filter(
              (s) =>
                  s.name.includes(filter) ||
                  s.theme.includes(filter) ||
                  s.route.includes(filter),
          )
        : screenshots;

    if (screenshotsToCapture.length === 0) {
        console.error(`❌ No screenshots match filter: ${filter}`);
        process.exit(1);
    }

    // Display configuration
    console.log('🚀 Starting screenshot capture...');
    console.log(`📍 Base URL: ${baseURL}`);
    console.log(`💾 Output: ${outputDir}`);
    if (filter) {
        console.log(`🔍 Filter: ${filter}`);
    }
    console.log(
        `📸 Capturing ${screenshotsToCapture.length} screenshot(s)...\n`,
    );

    // Launch browser
    const browser = await chromium.launch({
        headless: true,
    });

    let successCount = 0;
    const failures: Array<{ config: ScreenshotConfig; error: unknown }> = [];

    try {
        // Capture each screenshot
        for (const [index, config] of screenshotsToCapture.entries()) {
            const progress = `[${index + 1}/${screenshotsToCapture.length}]`;
            console.log(`${progress} ${config.name} (${config.theme})...`);

            try {
                await capturePageScreenshot(
                    browser,
                    config,
                    baseURL,
                    outputDir,
                );
                successCount++;
            } catch (error) {
                failures.push({ config, error });
            }
        }
    } finally {
        await browser.close();
    }

    // Display summary
    console.log('\n' + '='.repeat(50));
    console.log(`✅ Success: ${successCount}/${screenshotsToCapture.length}`);

    if (failures.length > 0) {
        console.log(
            `❌ Failed: ${failures.length}/${screenshotsToCapture.length}`,
        );
        failures.forEach(({ config, error }) => {
            console.log(
                `  - ${config.name}: ${error instanceof Error ? error.message : error}`,
            );
        });
        process.exit(1);
    }

    console.log('\n✅ All screenshots captured successfully!');
}

// Run the script
main().catch((error) => {
    console.error('\n❌ Fatal error:', error);
    process.exit(1);
});
