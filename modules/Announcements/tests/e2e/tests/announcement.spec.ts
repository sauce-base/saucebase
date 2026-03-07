import { expect, test } from '@e2e/fixtures';

test.describe('Announcement Banner', () => {
    test.describe.configure({ mode: 'serial' });

    test.beforeEach(async ({ laravel }) => {
        await laravel.callFunction(
            'Modules\\Announcements\\Tests\\Support\\AnnouncementTestHelper::clean',
        );
    });

    test('banner is visible on public page when active and show_on_frontend is true', async ({
        page,
        laravel,
    }) => {
        await laravel.factory('Modules\\Announcements\\Models\\Announcement', {
            text: 'Hello from announcement!',
            is_active: true,
            show_on_frontend: true,
        });

        await page.goto('/');

        await expect(page.getByText('Hello from announcement!')).toBeVisible();
    });

    test('banner is not visible when show_on_frontend is false on public page', async ({
        page,
        laravel,
    }) => {
        await laravel.factory('Modules\\Announcements\\Models\\Announcement', {
            text: 'Hidden on frontend',
            is_active: true,
            show_on_frontend: false,
        });

        await page.goto('/');

        await expect(page.getByText('Hidden on frontend')).not.toBeVisible();
    });

    test('banner is visible on dashboard when active and show_on_dashboard is true', async ({
        page,
        laravel,
        credentials,
        loginAs,
    }) => {
        await laravel.factory('Modules\\Announcements\\Models\\Announcement', {
            text: 'Dashboard announcement',
            is_active: true,
            show_on_dashboard: true,
        });

        await loginAs(credentials.user);
        await page.goto('/dashboard');

        await expect(page.getByText('Dashboard announcement')).toBeVisible();
    });

    test('banner is not visible on dashboard when show_on_dashboard is false', async ({
        page,
        laravel,
        credentials,
        loginAs,
    }) => {
        await laravel.factory('Modules\\Announcements\\Models\\Announcement', {
            text: 'Hidden on dashboard',
            is_active: true,
            show_on_dashboard: false,
        });

        await loginAs(credentials.user);
        await page.goto('/dashboard');

        await expect(page.getByText('Hidden on dashboard')).not.toBeVisible();
    });

    test('dismiss button hides the banner and banner does not reappear on reload', async ({
        page,
        laravel,
    }) => {
        await laravel.factory('Modules\\Announcements\\Models\\Announcement', {
            text: 'Dismissable banner',
            is_active: true,
            is_dismissable: true,
        });

        await page.goto('/');
        await expect(page.getByText('Dismissable banner')).toBeVisible();

        await page
            .getByRole('button', { name: 'Dismiss announcement' })
            .click();
        await expect(page.getByText('Dismissable banner')).not.toBeVisible();

        await page.reload();
        await expect(page.getByText('Dismissable banner')).not.toBeVisible();
    });

    test('no dismiss button when is_dismissable is false', async ({
        page,
        laravel,
    }) => {
        await laravel.factory('Modules\\Announcements\\Models\\Announcement', {
            text: 'Non-dismissable banner',
            is_active: true,
            is_dismissable: false,
        });

        await page.goto('/');
        await expect(page.getByText('Non-dismissable banner')).toBeVisible();
        await expect(
            page.getByRole('button', { name: 'Dismiss announcement' }),
        ).not.toBeVisible();
    });

    test('banner is visible when current time is within the schedule window', async ({
        page,
        laravel,
    }) => {
        await laravel.factory('Modules\\Announcements\\Models\\Announcement', {
            text: 'Scheduled announcement',
            is_active: true,
            show_on_frontend: true,
            starts_at: '2026-06-01 00:00:00',
            ends_at: '2026-06-30 23:59:59',
        });

        await laravel.travel('2026-06-15 12:00:00');

        await page.goto('/');

        await expect(page.getByText('Scheduled announcement')).toBeVisible();
    });

    test('banner is not visible when current time is outside the schedule window', async ({
        page,
        laravel,
    }) => {
        await laravel.factory('Modules\\Announcements\\Models\\Announcement', {
            text: 'Scheduled announcement',
            is_active: true,
            show_on_frontend: true,
            starts_at: '2026-06-01 00:00:00',
            ends_at: '2026-06-30 23:59:59',
        });

        await laravel.travel('2026-07-01 00:00:00');

        await page.goto('/');

        await expect(
            page.getByText('Scheduled announcement'),
        ).not.toBeVisible();
    });
});
