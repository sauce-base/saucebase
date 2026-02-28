import { expect, test } from '@playwright/test';
import { LoginPage } from '../../pages/LoginPage';

test.describe('Login Social Authentication', () => {
    let loginPage: LoginPage;

    test.beforeEach(async ({ page }) => {
        loginPage = new LoginPage(page);
        await loginPage.goto();
    });

    test('displays social login providers', async ({ page }) => {
        const googleButton = page.getByRole('link', {
            name: /connect with google/i,
        });
        const githubButton = page.getByRole('link', {
            name: /connect with github/i,
        });

        await expect(googleButton).toBeVisible();
        await expect(githubButton).toBeVisible();
    });

    test('initiates Google OAuth flow', async ({ page }) => {
        const googleButton = page.getByRole('link', {
            name: /connect with google/i,
        });
        await expect(googleButton).toBeVisible();

        const href = await googleButton.getAttribute('href');
        expect(href).toContain('/auth/socialite/google');
    });

    test('initiates GitHub OAuth flow', async ({ page }) => {
        const githubButton = page.getByRole('link', {
            name: /connect with github/i,
        });
        await expect(githubButton).toBeVisible();

        const href = await githubButton.getAttribute('href');
        expect(href).toContain('/auth/socialite/github');
    });
});
