import { expect, type Locator, type Page } from '@playwright/test';

export class VerifyEmailPage {
    readonly page: Page;
    readonly resendButton: Locator;
    readonly logoutButton: Locator;
    readonly verifyEmailEndpoint: string;

    constructor(page: Page) {
        this.page = page;
        this.verifyEmailEndpoint = '/auth/verify-email';
        this.resendButton = page.getByTestId('resend-verification-button');
        this.logoutButton = page.getByTestId('logout-button');
    }

    async goto() {
        await this.page.goto(this.verifyEmailEndpoint);
    }

    async expectToBeVisible() {
        await expect(this.page.getByTestId('verify-email-form')).toBeVisible();
    }

    async expectRedirectTo(url: string) {
        await expect(this.page).toHaveURL(url);
    }
}
