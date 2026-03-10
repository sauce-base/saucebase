import { expect, test } from '@e2e/fixtures';

test.describe('Dashboard page', () => {
    test('redirects unauthenticated user to login', async ({ page }) => {
        await page.goto('/dashboard');

        await expect(page).toHaveURL('/auth/login');
    });

    test('loads successfully when authenticated', async ({
        page,
        loginAs,
        credentials,
    }) => {
        await loginAs(credentials.user);

        const response = await page.goto('/dashboard');

        expect(response?.ok()).toBe(true);
        await expect(page).toHaveURL('/dashboard');
    });

    test('user menu is present', async ({ page, loginAs, credentials }) => {
        await loginAs(credentials.user);
        await page.goto('/dashboard');

        await expect(
            page.locator('[data-testid="user-menu-trigger"]'),
        ).toBeVisible();
    });
});
