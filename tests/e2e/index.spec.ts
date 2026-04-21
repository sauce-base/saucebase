import { expect, test } from '@playwright/test';
import { expectInertiaPageDataEmbedded } from './helpers/ssr';

test.describe('Landing page', () => {
    test('responds successfully when navigating to root', async ({ page }) => {
        const response = await page.goto('/');

        expect(response, 'Expected a navigation response').toBeTruthy();
        expect(
            response?.ok(),
            'Expected a successful status code',
        ).toBeTruthy();
    });

    test('Inertia page data is embedded for SSR/SEO', async ({ browser }) => {
        // Create a context with JavaScript disabled to verify SSR data embedding
        const context = await browser.newContext({
            javaScriptEnabled: false,
        });
        const page = await context.newPage();

        await page.goto('/');

        // Verify Inertia page data is properly embedded
        // (Search engines can execute this data, even though browsers without JS can't)
        await expectInertiaPageDataEmbedded(page);

        await context.close();
    });
});
