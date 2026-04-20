import { expect, test } from '@e2e/fixtures';

test.describe('Themes config flags', () => {
    test.beforeEach(async ({ laravel }) => {
        await laravel.callFunction('Modules\\Themes\\Tests\\Support\\ThemesTestHelper::cleanUserThemes');
        await laravel.config('themes.enabled', true);
    });

    test.afterEach(async ({ laravel }) => {
        await laravel.callFunction('Modules\\Themes\\Tests\\Support\\ThemesTestHelper::cleanUserThemes');
        await laravel.config('themes.enabled', true);
    });

    // ── themes.enabled ────────────────────────────────────────────────────────

    test('theme panel trigger is hidden when themes.enabled is false', async ({ page, laravel }) => {
        await laravel.config('themes.enabled', false);
        await page.goto('/');

        await expect(page.getByTestId('theme-panel-trigger')).not.toBeVisible();
    });

    test('theme panel trigger is visible when themes.enabled is true', async ({ page, laravel }) => {
        await laravel.config('themes.enabled', true);
        await page.goto('/');

        await expect(page.getByTestId('theme-panel-trigger')).toBeVisible();
    });
});
