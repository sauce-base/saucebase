import { expect, test } from '@e2e/fixtures';

test.describe('Roadmap — auth-required page', () => {
    test.describe.configure({ mode: 'serial' });

    test.beforeEach(async ({ laravel }) => {
        await laravel.callFunction('Modules\\Roadmap\\Tests\\Support\\RoadmapTestHelper::clean');
    });

    test('guest is redirected to login page', async ({ page }) => {
        await page.goto('/roadmap');

        await expect(page).toHaveURL(/login/);
    });

    test('approved item is visible on the roadmap', async ({ page, laravel, credentials, loginAs }) => {
        await laravel.factory('Modules\\Roadmap\\Models\\RoadmapItem', {
            title: 'Dark mode support',
            status: 'approved',
            type: 'feature',
        });

        await loginAs(credentials.user);
        await page.goto('/roadmap');
        await page.waitForLoadState('networkidle');

        await expect(page.getByText('Dark mode support')).toBeVisible();
    });

    test('pending approval item is not visible on the roadmap', async ({ page, laravel, credentials, loginAs }) => {
        await laravel.factory('Modules\\Roadmap\\Models\\RoadmapItem', {
            title: 'Hidden suggestion',
            status: 'pending_approval',
            type: 'feature',
        });

        await loginAs(credentials.user);
        await page.goto('/roadmap');

        await expect(page.getByText('Hidden suggestion')).not.toBeVisible();
    });

    test('rejected item is not visible on the roadmap', async ({ page, laravel, credentials, loginAs }) => {
        await laravel.factory('Modules\\Roadmap\\Models\\RoadmapItem', {
            title: 'Rejected idea',
            status: 'rejected',
            type: 'feature',
        });

        await loginAs(credentials.user);
        await page.goto('/roadmap');

        await expect(page.getByText('Rejected idea')).not.toBeVisible();
    });

    test('in_progress item is visible on the roadmap', async ({ page, laravel, credentials, loginAs }) => {
        await laravel.factory('Modules\\Roadmap\\Models\\RoadmapItem', {
            title: 'Work in progress',
            status: 'in_progress',
            type: 'feature',
        });

        await loginAs(credentials.user);
        await page.goto('/roadmap');

        await expect(page.getByText('Work in progress')).toBeVisible();
    });

    test('completed item is visible on the roadmap', async ({ page, laravel, credentials, loginAs }) => {
        await laravel.factory('Modules\\Roadmap\\Models\\RoadmapItem', {
            title: 'Done feature',
            status: 'completed',
            type: 'feature',
        });

        await loginAs(credentials.user);
        await page.goto('/roadmap');

        await expect(page.getByText('Done feature')).toBeVisible();
    });

    test('authenticated user sees suggest a feature button', async ({ page, credentials, loginAs }) => {
        await loginAs(credentials.user);
        await page.goto('/roadmap');
        await page.waitForLoadState('networkidle');

        await expect(page.getByTestId('suggest-btn')).toBeVisible();
    });
});

test.describe('Roadmap — voting (no page reload)', () => {
    test.describe.configure({ mode: 'serial' });

    test.beforeEach(async ({ laravel }) => {
        await laravel.callFunction('Modules\\Roadmap\\Tests\\Support\\RoadmapTestHelper::clean');
    });

    test('vote count increments instantly without full page reload', async ({
        page,
        laravel,
        credentials,
        loginAs,
    }) => {
        const item = await laravel.factory('Modules\\Roadmap\\Models\\RoadmapItem', {
            title: 'Votable feature',
            status: 'approved',
            type: 'feature',
        });

        await loginAs(credentials.user);
        await page.goto('/roadmap');
        await page.waitForLoadState('networkidle');

        const voteScore = page.getByTestId(`vote-score-${item.id}`);
        await expect(voteScore).toHaveText('0');

        // Plant a marker on the window — survives Inertia SPA navigations (pushState)
        // but is wiped out by a real full page reload
        await page.evaluate(() => { (window as any).__noReload = true; });

        await page.getByTestId(`upvote-btn-${item.id}`).click();

        // Score updates to 1
        await expect(voteScore).toHaveText('1');

        // Marker must still be present — a real reload would have cleared it
        expect(await page.evaluate(() => (window as any).__noReload)).toBe(true);
    });

    test('upvote button shows active state after voting', async ({
        page,
        laravel,
        credentials,
        loginAs,
    }) => {
        const item = await laravel.factory('Modules\\Roadmap\\Models\\RoadmapItem', {
            title: 'Active vote feature',
            status: 'approved',
            type: 'feature',
        });

        await loginAs(credentials.user);
        await page.goto('/roadmap');
        await page.waitForLoadState('networkidle');

        const voteBox = page.getByTestId(`vote-box-${item.id}`);
        await expect(voteBox).toHaveAttribute('data-user-vote', 'none');

        await page.getByTestId(`upvote-btn-${item.id}`).click();

        await expect(voteBox).toHaveAttribute('data-user-vote', 'up');
    });

    test('vote score decrements instantly when toggling upvote off', async ({
        page,
        laravel,
        credentials,
        loginAs,
    }) => {
        const item = await laravel.factory('Modules\\Roadmap\\Models\\RoadmapItem', {
            title: 'Toggle vote feature',
            status: 'approved',
            type: 'feature',
        });

        await loginAs(credentials.user);
        await page.goto('/roadmap');
        await page.waitForLoadState('networkidle');

        const upvoteBtn = page.getByTestId(`upvote-btn-${item.id}`);
        const voteScore = page.getByTestId(`vote-score-${item.id}`);

        // Upvote
        await upvoteBtn.click();
        await expect(voteScore).toHaveText('1');
        await page.waitForLoadState('networkidle');

        // Plant a marker — survives Inertia SPA navigations but not a full reload
        await page.evaluate(() => { (window as any).__noReload = true; });

        // Toggle off
        await upvoteBtn.click();
        await expect(voteScore).toHaveText('0');
        await expect(page.getByTestId(`vote-box-${item.id}`)).toHaveAttribute('data-user-vote', 'none');

        expect(await page.evaluate(() => (window as any).__noReload)).toBe(true);
    });
});
