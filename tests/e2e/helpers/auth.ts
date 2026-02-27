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
    user: { email: string; password: string },
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
