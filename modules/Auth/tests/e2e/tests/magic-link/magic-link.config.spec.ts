import { test, expect } from '@e2e/fixtures';
import { LoginPage } from '../../pages/LoginPage';

test.describe.parallel('Magic Link Config', () => {
    test('magic link page returns 404 when feature is disabled', async ({
        page,
        laravel,
    }) => {
        await laravel.config('auth.magic_link.enabled', false);

        const response = await page.goto('/auth/magic-link');

        expect(response?.status()).toBe(404);
    });

    test('magic link is hidden on login page when feature is disabled', async ({
        page,
        laravel,
    }) => {
        await laravel.config('auth.magic_link.enabled', false);

        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.expectToBeVisible();

        await expect(page.getByTestId('magic-link-login-link')).not.toBeVisible();
    });
});
