import { expect, type Locator, type Page } from '@playwright/test';

export class MagicLinkPage {
    readonly page: Page;
    readonly emailInput: Locator;
    readonly submitButton: Locator;
    readonly backToLoginLink: Locator;
    readonly endpoint: string;

    constructor(page: Page) {
        this.page = page;
        this.endpoint = '/auth/magic-link';
        this.emailInput = page.getByTestId('magic-link-email');
        this.submitButton = page.getByTestId('magic-link-submit');
        this.backToLoginLink = page.getByTestId('back-to-login-link');
    }

    async goto() {
        await this.page.goto(this.endpoint);
    }

    async expectToBeVisible() {
        await expect(this.page.getByTestId('magic-link-form')).toBeVisible();
        await expect(this.emailInput).toBeVisible();
        await expect(this.submitButton).toBeVisible();
    }

    async requestLink(email: string) {
        await this.emailInput.fill(email);
        await this.submitButton.click();
    }
}
