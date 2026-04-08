import { expect, test } from '@e2e/fixtures';

test.describe('Themes config flags', () => {
    test.beforeEach(async ({ laravel }) => {
        await laravel.callFunction('Modules\\Themes\\Tests\\Support\\ThemesTestHelper::cleanUserThemes');
        // Restore defaults after any config overrides
        await laravel.callFunction('Modules\\Themes\\Tests\\Support\\ThemesTestHelper::resetConfig');
    });

    test.afterEach(async ({ laravel }) => {
        await laravel.callFunction('Modules\\Themes\\Tests\\Support\\ThemesTestHelper::cleanUserThemes');
        await laravel.callFunction('Modules\\Themes\\Tests\\Support\\ThemesTestHelper::resetConfig');
    });

    // ── themes.enabled ────────────────────────────────────────────────────────

    test('theme panel trigger is hidden when themes.enabled is false', async ({ page, laravel }) => {
        await laravel.callFunction('Modules\\Themes\\Tests\\Support\\ThemesTestHelper::setEnabled', [false]);
        await page.goto('/');

        await expect(page.getByTestId('theme-panel-trigger')).not.toBeVisible();
    });

    test('theme panel trigger is visible when themes.enabled is true', async ({ page, laravel }) => {
        await laravel.callFunction('Modules\\Themes\\Tests\\Support\\ThemesTestHelper::setEnabled', [true]);
        await page.goto('/');

        await expect(page.getByTestId('theme-panel-trigger')).toBeVisible();
    });
});
