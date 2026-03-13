import { test, expect } from '@e2e/fixtures';
import { LoginPage } from '../../pages/LoginPage';

test.describe.parallel('Logout Basics', () => {
    let loginPage: LoginPage;

    test.beforeEach(async ({ page }) => {
        loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.expectToBeVisible();
    });

    test('logs out from user menu and redirects to login', async ({ page, credentials }) => {
        const user = credentials.user;

        // Login first
        await loginPage.login(user.email, user.password);
        await expect(page).toHaveURL('/dashboard');

        // Open user menu using the test ID
        const userMenuTrigger = page.getByTestId('user-menu-trigger');
        await userMenuTrigger.click();
        await page.waitForTimeout(300);

        // Click the 'Log out' menu item
        await page.getByRole('menuitem', { name: /log out/i }).click();

        // Confirm dialog should appear
        const confirmDialog = page.getByTestId('confirm-dialog');
        await expect(confirmDialog).toBeVisible();

        // Confirm logout
        await page.getByTestId('confirm-dialog-confirm').click();

        // After logout we expect to be redirected to the home page
        await expect(page).toHaveURL('/');

        // Visiting protected route should redirect back to login
        await page.goto('/dashboard');
        await expect(page).toHaveURL('/auth/login');
    });

    test('clicking outside the logout dialog does not dismiss it', async ({ page, credentials }) => {
        const user = credentials.user;

        await loginPage.login(user.email, user.password);
        await expect(page).toHaveURL('/dashboard');

        const userMenuTrigger = page.getByTestId('user-menu-trigger');
        await userMenuTrigger.click();
        await page.waitForTimeout(300);

        await page.getByRole('menuitem', { name: /log out/i }).click();

        const confirmDialog = page.getByTestId('confirm-dialog');
        await expect(confirmDialog).toBeVisible();

        // Click the overlay (outside the dialog)
        await page.mouse.click(10, 10);

        // Dialog should still be visible
        await expect(confirmDialog).toBeVisible();
    });

    test('cancelling logout dialog keeps user logged in', async ({ page, credentials }) => {
        const user = credentials.user;

        // Login first
        await loginPage.login(user.email, user.password);
        await expect(page).toHaveURL('/dashboard');

        // Open user menu
        const userMenuTrigger = page.getByTestId('user-menu-trigger');
        await userMenuTrigger.click();
        await page.waitForTimeout(300);

        // Click the 'Log out' menu item
        await page.getByRole('menuitem', { name: /log out/i }).click();

        // Confirm dialog should appear
        const confirmDialog = page.getByTestId('confirm-dialog');
        await expect(confirmDialog).toBeVisible();

        // Cancel logout
        await page.getByTestId('confirm-dialog-cancel').click();

        // Dialog should be closed
        await expect(confirmDialog).not.toBeVisible();

        // User should still be on dashboard
        await expect(page).toHaveURL('/dashboard');
    });
});
