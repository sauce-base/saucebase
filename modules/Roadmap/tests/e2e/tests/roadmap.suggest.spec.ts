import { expect, test } from '@e2e/fixtures';

test.describe('Roadmap — suggest a feature (dialog)', () => {
    test.describe.configure({ mode: 'serial' });

    test('authenticated user sees the suggest button', async ({ page, credentials, loginAs }) => {
        await loginAs(credentials.user);
        await page.goto('/roadmap');
        await page.waitForLoadState('networkidle');

        await expect(page.getByTestId('suggest-btn')).toBeVisible();
    });

    test('clicking suggest button opens the dialog', async ({ page, credentials, loginAs }) => {
        await loginAs(credentials.user);
        await page.goto('/roadmap');
        await page.waitForLoadState('networkidle');

        await page.getByTestId('suggest-btn').click();

        await expect(page.getByRole('dialog')).toBeVisible();
    });

    test('cancel button closes the dialog', async ({ page, credentials, loginAs }) => {
        await loginAs(credentials.user);
        await page.goto('/roadmap');
        await page.waitForLoadState('networkidle');

        await page.getByTestId('suggest-btn').click();
        await expect(page.getByRole('dialog')).toBeVisible();

        await page.getByTestId('suggest-cancel-btn').click();
        await expect(page.getByRole('dialog')).not.toBeVisible();
    });

    test('authenticated user can submit a suggestion via the dialog', async ({
        page,
        credentials,
        loginAs,
    }) => {
        await loginAs(credentials.user);
        await page.goto('/roadmap');
        await page.waitForLoadState('networkidle');

        await page.getByTestId('suggest-btn').click();

        await page.getByTestId('suggest-title').fill('My awesome dialog idea');
        await page.getByTestId('suggest-description').fill('This would be great.');
        await page.getByTestId('suggest-submit-btn').click();

        // Dialog closes and user stays on roadmap
        await expect(page.getByRole('dialog')).not.toBeVisible();
        await expect(page).toHaveURL('/roadmap');
    });

    test('form is reset after successful submission', async ({
        page,
        credentials,
        loginAs,
    }) => {
        await loginAs(credentials.user);
        await page.goto('/roadmap');
        await page.waitForLoadState('networkidle');

        // First submission
        await page.getByTestId('suggest-btn').click();
        await page.getByTestId('suggest-title').fill('First suggestion');
        await page.getByTestId('suggest-submit-btn').click();
        await expect(page.getByRole('dialog')).not.toBeVisible();
        await page.waitForLoadState('networkidle');

        // Open dialog again — form should be empty
        await page.getByTestId('suggest-btn').click();
        await expect(page.getByTestId('suggest-title')).toHaveValue('');
        await expect(page.getByTestId('suggest-description')).toHaveValue('');
    });

    test('submitted suggestion is not immediately visible on roadmap', async ({
        page,
        credentials,
        loginAs,
    }) => {
        await loginAs(credentials.user);
        await page.goto('/roadmap');
        await page.waitForLoadState('networkidle');

        await page.getByTestId('suggest-btn').click();
        await page.getByTestId('suggest-title').fill('Invisible until approved');
        await page.getByTestId('suggest-submit-btn').click();

        await expect(page.getByRole('dialog')).not.toBeVisible();
        await expect(page.getByText('Invisible until approved')).not.toBeVisible();
    });
});
