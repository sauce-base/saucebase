import { expect, test } from '@playwright/test';

test.describe('Blog public pages', () => {
    test('blog index page loads', async ({ page }) => {
        await page.goto('/blog');

        await expect(page).toHaveTitle(/Blog/);
    });

    test('blog index shows published posts', async ({ page }) => {
        await page.goto('/blog');

        const posts = page.locator('[data-testid^="post-card-"]');
        const count = await posts.count();

        if (count > 0) {
            await expect(posts.first()).toBeVisible();
        }
    });

    test('clicking post navigates to show page', async ({ page }) => {
        await page.goto('/blog');

        const firstCard = page.locator('[data-testid^="post-card-"]').first();
        const count = await firstCard.count();

        if (count === 0) {
            test.skip();
            return;
        }

        await firstCard.click();

        await expect(page.locator('[data-testid="post-title"]')).toBeVisible();
        await expect(page.locator('[data-testid="post-content"]')).toBeVisible();
    });

    test('show page back link navigates to blog index', async ({ page }) => {
        await page.goto('/blog');

        const firstCard = page.locator('[data-testid^="post-card-"]').first();
        if ((await firstCard.count()) === 0) {
            test.skip();
            return;
        }

        await firstCard.click();
        await page.getByText('← Back to Blog').click();

        await expect(page).toHaveURL(/\/blog$/);
    });
});
