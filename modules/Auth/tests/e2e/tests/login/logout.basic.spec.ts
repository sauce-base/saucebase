import { test, expect } from '@e2e/fixtures';

test.describe.parallel('Logout Basics', () => {
    test('logs out from user menu and redirects to login', async ({ page, credentials, loginAs }) => {
        const user = credentials.user;

        await loginAs(user);
        await page.goto('/dashboard');
        await expect(page).toHaveURL('/dashboard');

        // Open user menu using the test ID
        const userMenuTrigger = page.getByTestId('user-menu-trigger');
        await userMenuTrigger.click();

        // Wait for dropdown to be visible before clicking
        const logoutMenuItem = page.getByTestId('nav-action-logout');
        await expect(logoutMenuItem).toBeVisible();
        await logoutMenuItem.click();

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

    test('clicking outside the logout dialog does not dismiss it', async ({ page, credentials, loginAs }) => {
        const user = credentials.user;

        await loginAs(user);
        await page.goto('/dashboard');
        await expect(page).toHaveURL('/dashboard');

        const userMenuTrigger = page.getByTestId('user-menu-trigger');
        await userMenuTrigger.click();

        const logoutMenuItem = page.getByTestId('nav-action-logout');
        await expect(logoutMenuItem).toBeVisible();
        await logoutMenuItem.click();

        const confirmDialog = page.getByTestId('confirm-dialog');
        await expect(confirmDialog).toBeVisible();

        // Click the overlay (outside the dialog)
        await page.mouse.click(10, 10);

        // Dialog should still be visible
        await expect(confirmDialog).toBeVisible();
    });

    test('cancelling logout dialog keeps user logged in', async ({ page, credentials, loginAs }) => {
        const user = credentials.user;

        await loginAs(user);
        await page.goto('/dashboard');
        await expect(page).toHaveURL('/dashboard');

        // Open user menu
        const userMenuTrigger = page.getByTestId('user-menu-trigger');
        await userMenuTrigger.click();

        // Wait for dropdown to be visible before clicking
        const logoutMenuItem = page.getByTestId('nav-action-logout');
        await expect(logoutMenuItem).toBeVisible();
        await logoutMenuItem.click();

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
