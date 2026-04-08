import { expect, test } from '@e2e/fixtures';

const tinyPng = Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
    'base64',
);

test.describe('Profile Avatar', () => {
    test('redirects unauthenticated user to login', async ({ page }) => {
        await page.goto('/settings/profile/edit');

        await expect(page).toHaveURL('/auth/login');
    });

    test('spinner clears after avatar upload', async ({ page, loginAs, credentials }) => {
        await loginAs(credentials.user);
        await page.goto('/settings/profile/edit');

        await page.setInputFiles('input[type="file"]', {
            name: 'avatar.png',
            mimeType: 'image/png',
            buffer: tinyPng,
        });

        const loadingOverlay = page.locator('div.rounded-xl.bg-black\\/50');

        // Spinner must appear while the upload request is in flight
        await expect(loadingOverlay).toBeVisible();

        // Spinner must clear after onFinish runs (regression: was stuck before the fix)
        await expect(loadingOverlay).not.toBeVisible({ timeout: 10000 });
    });

    test('delete avatar dialog opens and closes', async ({ page, loginAs, credentials }) => {
        await loginAs(credentials.user);
        await page.goto('/settings/profile/edit');

        // Upload an avatar so the trash button becomes visible
        await page.setInputFiles('input[type="file"]', {
            name: 'avatar.png',
            mimeType: 'image/png',
            buffer: tinyPng,
        });

        // Wait for upload to complete
        const loadingOverlay = page.locator('div.rounded-xl.bg-black\\/50');
        await expect(loadingOverlay).toBeVisible();
        await expect(loadingOverlay).not.toBeVisible({ timeout: 10000 });

        // Click the trash button (only visible when hasUploadedAvatar is true)
        await page.locator('button.bg-red-500').click();

        // Confirm delete dialog is visible
        const dialog = page.getByRole('dialog');
        await expect(dialog).toBeVisible();

        // Click Cancel and verify the dialog closes
        await dialog.getByRole('button', { name: /cancel/i }).click();
        await expect(dialog).not.toBeVisible();
    });
});
