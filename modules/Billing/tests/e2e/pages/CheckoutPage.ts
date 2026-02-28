import { expect, type Locator, type Page } from '@playwright/test';

export class CheckoutPage {
    readonly page: Page;
    readonly checkoutForm: Locator;
    readonly nameInput: Locator;
    readonly emailInput: Locator;
    readonly submitButton: Locator;
    readonly orderSummary: Locator;
    readonly productName: Locator;

    constructor(page: Page) {
        this.page = page;
        this.checkoutForm = page.getByTestId('checkout-form');
        this.nameInput = page.getByTestId('checkout-name');
        this.emailInput = page.getByTestId('checkout-email');
        this.submitButton = page.getByTestId('checkout-submit');
        this.orderSummary = page.getByTestId('order-summary');
        this.productName = page.getByTestId('checkout-product-name');
    }

    async waitForCheckoutPage() {
        await this.page.waitForURL(/\/billing\/checkout\/.+/);
    }

    async expectProductName(name: string) {
        await expect(this.productName).toHaveText(name);
    }

    async expectFormVisible() {
        await expect(this.checkoutForm).toBeVisible();
        await expect(this.nameInput).toBeVisible();
        await expect(this.emailInput).toBeVisible();
        await expect(this.submitButton).toBeVisible();
    }

    async mockSubmitWithStripeRedirect() {
        await this.page.route('**/billing/checkout/**', (route) => {
            if (route.request().method() === 'POST') {
                route.fulfill({
                    status: 409,
                    headers: {
                        'X-Inertia-Location':
                            'https://checkout.stripe.com/test',
                    },
                    body: '',
                });
            } else {
                route.continue();
            }
        });
    }

    async submit() {
        await this.submitButton.click();
    }
}
