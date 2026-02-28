import {
    expect,
    type Locator,
    type Page,
    type Request,
    type Response,
} from '@playwright/test';

export class ForgotPasswordPage {
    readonly page: Page;
    readonly emailInput: Locator;
    readonly resetButton: Locator;
    readonly loginLink: Locator;
    readonly passwordResetEndpoint: string;

    constructor(page: Page) {
        this.page = page;
        this.passwordResetEndpoint = '/auth/forgot-password';
        this.emailInput = page.getByTestId('email');
        this.resetButton = page.getByTestId('reset-button');
        this.loginLink = page.getByTestId('back-to-login-link');
    }

    async goto() {
        await this.page.goto(this.passwordResetEndpoint);
    }

    async resetPassword(email: string) {
        await this.emailInput.fill(email);
        await this.resetButton.click();
    }

    async expectToBeVisible() {
        await expect(
            this.page.getByTestId('forgot-password-form'),
        ).toBeVisible();
        await expect(this.emailInput).toBeVisible();
        await expect(this.resetButton).toBeVisible();
    }

    async expectEmailError() {
        const emailError = this.page.getByTestId('email-error');
        await expect(emailError).toBeVisible();
    }

    async waitForPasswordResetResponse() {
        return this.page.waitForResponse((response: Response) =>
            response.url().includes(this.passwordResetEndpoint),
        );
    }

    async waitForFailedPasswordResetRequest() {
        return this.page.waitForEvent('requestfailed', (request: Request) =>
            request.url().includes(this.passwordResetEndpoint),
        );
    }

    async mockNetworkFailure() {
        await this.page.route(this.passwordResetEndpoint, (route) =>
            route.abort(),
        );
    }

    async mockServerResponse(
        status: number,
        body: Record<string, unknown> = {},
    ) {
        await this.page.route(this.passwordResetEndpoint, (route) => {
            route.fulfill({
                status,
                contentType: 'application/json',
                body: JSON.stringify(body),
            });
        });
    }

    async mockDelayedResponse(delayMs: number) {
        await this.page.route(this.passwordResetEndpoint, async (route) => {
            await new Promise((resolve) => setTimeout(resolve, delayMs));
            await route.continue();
        });
    }

    /**
     * Verify redirect to specific intended URL
     */
    async expectRedirectTo(url: string) {
        await expect(this.page).toHaveURL(url);
    }

    /**
     * Press Tab key for keyboard navigation
     */
    async pressTab() {
        await this.page.keyboard.press('Tab');
    }

    /**
     * Press Enter key
     */
    async pressEnter() {
        await this.page.keyboard.press('Enter');
    }
}
