import {
    expect,
    type Locator,
    type Page,
    type Request,
    type Response,
} from '@playwright/test';

export class RegisterPage {
    readonly page: Page;
    readonly nameInput: Locator;
    readonly emailInput: Locator;
    readonly passwordInput: Locator;
    readonly passwordToggle: Locator;
    readonly registerButton: Locator;
    readonly signupEndpoint: string;
    readonly redirectEndpoint: string;

    /** Indicates if email verification is required after registration */
    readonly userMustVerifyEmail: boolean = false;

    constructor(page: Page) {
        this.page = page;
        this.signupEndpoint = '/auth/register';
        this.redirectEndpoint = this.userMustVerifyEmail
            ? '/auth/verify-email'
            : '/dashboard';
        this.nameInput = page.getByTestId('name');
        this.emailInput = page.getByTestId('email');
        this.passwordInput = page.getByTestId('password');
        this.passwordToggle = page.getByTestId('password-toggle');
        this.registerButton = page.getByTestId('register-button');
    }

    async goto() {
        await this.page.goto(this.signupEndpoint);
    }

    async register(name: string, email: string, password: string) {
        await this.nameInput.fill(name);
        await this.emailInput.fill(email);
        await this.passwordInput.fill(password);

        await this.registerButton.click();
    }

    async expectToBeVisible() {
        await expect(this.page.getByTestId('register-form')).toBeVisible();
        await expect(this.nameInput).toBeVisible();
        await expect(this.emailInput).toBeVisible();
        await expect(this.passwordInput).toBeVisible();
        await expect(this.registerButton).toBeVisible();
    }

    async expectEmailError() {
        const emailError = this.page.getByTestId('email-error');
        await expect(emailError).toBeVisible();
    }

    async expectPasswordError() {
        const passwordError = this.page.getByTestId('password-error');
        await expect(passwordError).toBeVisible();
    }

    async togglePasswordVisibility() {
        await this.passwordToggle.click();
    }

    async expectPasswordVisible() {
        await expect(this.passwordInput).toHaveAttribute('type', 'text');
    }

    async expectPasswordHidden() {
        await expect(this.passwordInput).toHaveAttribute('type', 'password');
    }

    async waitForLoginResponse() {
        return this.page.waitForResponse((response: Response) =>
            response.url().includes(this.signupEndpoint),
        );
    }

    async waitForFailedLoginRequest() {
        return this.page.waitForEvent('requestfailed', (request: Request) =>
            request.url().includes(this.signupEndpoint),
        );
    }

    async mockNetworkFailure() {
        await this.page.route(this.signupEndpoint, (route) => route.abort());
    }

    async mockServerResponse(
        status: number,
        body: Record<string, unknown> = {},
    ) {
        await this.page.route(this.signupEndpoint, (route) => {
            route.fulfill({
                status,
                contentType: 'application/json',
                body: JSON.stringify(body),
            });
        });
    }

    async mockDelayedResponse(delayMs: number) {
        await this.page.route(this.signupEndpoint, async (route) => {
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

    /**
     * Get the currently focused element's test ID
     */
    async getFocusedElementTestId(): Promise<string | null> {
        return await this.page.evaluate(() => {
            const focused = document.activeElement;
            return focused?.getAttribute('data-testid') || null;
        });
    }
}
