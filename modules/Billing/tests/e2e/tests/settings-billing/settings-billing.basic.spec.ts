import { expect, test } from '@e2e/fixtures';
import { SettingsBillingPage } from '../../pages/SettingsBillingPage';

test.describe.parallel('Settings Billing Basics', () => {
    test('redirects unauthenticated user to login', async ({ page }) => {
        await page.goto('/settings/billing');
        await expect(page).toHaveURL('/auth/login');
    });

    test('shows empty state for user without active subscription', async ({
        page,
        loginAs,
        credentials,
    }) => {
        await loginAs(credentials.user);

        const billingPage = new SettingsBillingPage(page);
        await billingPage.goto();
        await billingPage.expectNoSubscription();
    });

    test('shows active subscription details', async ({
        page,
        loginAs,
        credentials,
    }) => {
        await loginAs(credentials.subscriber);

        const billingPage = new SettingsBillingPage(page);
        await billingPage.goto();
        await billingPage.expectPlanName('Pro');
        await expect(billingPage.cancelButton).toBeVisible();
    });

    test('opens and closes cancel dialog', async ({
        page,
        loginAs,
        credentials,
    }) => {
        await loginAs(credentials.subscriber);

        const billingPage = new SettingsBillingPage(page);
        await billingPage.goto();
        await billingPage.openCancelDialog();
        await billingPage.expectCancelDialogVisible();
        await billingPage.closeCancelDialog();
        await expect(billingPage.cancelDialogConfirm).not.toBeVisible();
    });

    test('shows resume button for pending cancellation', async ({
        page,
        loginAs,
        credentials,
    }) => {
        await loginAs(credentials.cancelled);

        const billingPage = new SettingsBillingPage(page);
        await billingPage.goto();
        await expect(billingPage.resumeButton).toBeVisible();
        await expect(page.getByText('Cancels on')).toBeVisible();
    });
});
