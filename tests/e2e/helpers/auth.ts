import type { UserCredential } from '@e2e/fixtures/index.ts';
import type { Page } from '@playwright/test';
import type { Laravel } from '@saucebase/laravel-playwright';

type SessionCookie = {
    name: string;
    value: string;
    domain: string;
    path: string;
};

export async function loginAs(
    page: Page,
    laravel: Laravel,
    user: UserCredential,
): Promise<void> {
    const cookie = await laravel.callFunction<SessionCookie>(
        'Tests\\Support\\AuthHelper::loginAs',
        [user.email],
    );

    await page.context().addCookies([
        {
            ...cookie,
            httpOnly: true,
            sameSite: 'Lax',
        },
    ]);
}
