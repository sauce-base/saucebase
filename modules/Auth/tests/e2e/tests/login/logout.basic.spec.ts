import { expect, test } from '@e2e/fixtures';
import { LoginPage } from '../../pages/LoginPage';

test.describe.parallel('Logout Basics', () => {
    let loginPage: LoginPage;

    test.beforeEach(async ({ page }) => {
        loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.expectToBeVisible();
    });

    test('logs out from user menu and redirects to login', async ({
        page,
        credentials,
    }) => {
        const user = credentials.user;

        // Login first
        await loginPage.login(user.email, user.password);
        await expect(page).toHaveURL('/dashboard');

        // Open user menu using the test ID
        const userMenuTrigger = page.getByTestId('user-menu-trigger');
        await userMenuTrigger.click();
        await page.waitForTimeout(300);

        // Click the 'Log out' menu item
        page.on('dialog', (dialog) => dialog.accept());

        await page.getByRole('menuitem', { name: /log out/i }).click();

        // After logout we expect to be redirected to the home page
        await expect(page).toHaveURL('/');

        // Visiting protected route should redirect back to login
        await page.goto('/dashboard');
        await expect(page).toHaveURL('/auth/login');
    });
});
