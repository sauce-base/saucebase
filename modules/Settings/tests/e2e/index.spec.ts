import { expect, test } from '@e2e/fixtures';

test.describe('Settings index page', () => {
    test('responds successfully when navigating to root', async ({ page }) => {
        const response = await page.goto('/');

        expect(response, 'Expected a navigation response').toBeTruthy();
        expect(
            response?.ok(),
            'Expected a successful status code',
        ).toBeTruthy();
    });
});
