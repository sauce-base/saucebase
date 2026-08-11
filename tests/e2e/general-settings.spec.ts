import { expect, test } from '@e2e/fixtures';

const setSetting = (
    laravel: { query: (sql: string, bindings: string[]) => Promise<unknown> },
    name: string,
    payload: string,
) =>
    laravel.query('UPDATE settings SET payload = ? WHERE name = ?', [
        payload,
        name,
    ]);

test.describe('General Settings branding', () => {
    test.describe.configure({ mode: 'serial' });

    test('shares configured branding with the public frontend', async ({
        page,
        laravel,
    }) => {
        await setSetting(laravel, 'site_name', '"Acme Platform"');
        await setSetting(laravel, 'site_tagline', '"Everything you need"');
        await setSetting(
            laravel,
            'site_description',
            '"The Acme customer platform."',
        );

        try {
            await page.goto('/');

            await expect(page).toHaveTitle(
                'Everything you need - Acme Platform',
            );
            await expect(page.getByTestId('footer-app-name')).toContainText(
                'Acme Platform',
            );
            await expect(page.getByTestId('app-description')).toHaveAttribute(
                'content',
                'The Acme customer platform.',
            );
        } finally {
            await setSetting(laravel, 'site_name', '"Saucebase"');
            await setSetting(laravel, 'site_tagline', 'null');
            await setSetting(laravel, 'site_description', 'null');
        }
    });

    test('falls back to the site name alone when no tagline is set', async ({
        page,
        laravel,
    }) => {
        await setSetting(laravel, 'site_name', '"Acme Platform"');
        await setSetting(laravel, 'site_tagline', 'null');

        try {
            await page.goto('/');

            await expect(page).toHaveTitle('Acme Platform');
        } finally {
            await setSetting(laravel, 'site_name', '"Saucebase"');
        }
    });
});
