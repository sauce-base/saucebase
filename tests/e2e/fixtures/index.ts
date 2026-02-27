import { loginAs as doLoginAs } from '@e2e/helpers/auth.ts';
import { expect } from '@playwright/test';
import { test as base } from '@saucebase/laravel-playwright';

export type UserCredential = { email: string; password: string };

export type TestCredentials = {
    admin: UserCredential;
    user: UserCredential;
    subscriber: UserCredential;
    cancelled: UserCredential;
    [key: string]: UserCredential;
};

export const test = base.extend<{
    credentials: TestCredentials;
    loginAs: (user: UserCredential) => Promise<void>;
}>({
    credentials: async ({ laravel }, use) => {
        const creds = await laravel.callFunction<TestCredentials>(
            'Tests\\Support\\TestFixtures::credentials',
        );
        await use(creds);
    },
    loginAs: async ({ page, laravel }, use) => {
        await use((user) => doLoginAs(page, laravel, user));
    },
});

export { expect };
