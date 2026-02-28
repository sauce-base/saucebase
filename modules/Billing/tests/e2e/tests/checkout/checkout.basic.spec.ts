import { expect, test } from '@e2e/fixtures';
import { CheckoutPage } from '../../pages/CheckoutPage';

test.describe.parallel('Checkout Basics', () => {
    test('redirects guest accessing checkout URL to register', async ({
        page,
    }) => {
        await page.goto(
            '/billing/checkout/00000000-0000-0000-0000-000000000000',
        );
        await expect(page).toHaveURL('/auth/register');
    });

    test('renders checkout page with product details after login', async ({
        page,
        loginAs,
        credentials,
    }) => {
        await loginAs(credentials.admin);

        await page.goto('/');
        await page
            .locator(
                '[data-testid="product-card-pro"] [data-testid="get-started-button"]',
            )
            .click();

        const checkoutPage = new CheckoutPage(page);
        await checkoutPage.waitForCheckoutPage();

        await expect(checkoutPage.orderSummary).toBeVisible();
        await checkoutPage.expectProductName('Pro');
    });

    test('displays all billing form fields', async ({
        page,
        loginAs,
        credentials,
    }) => {
        await loginAs(credentials.admin);

        await page.goto('/');
        await page
            .locator(
                '[data-testid="product-card-pro"] [data-testid="get-started-button"]',
            )
            .click();

        const checkoutPage = new CheckoutPage(page);
        await checkoutPage.waitForCheckoutPage();
        await checkoutPage.expectFormVisible();
    });

    test('submits form and initiates Stripe redirect', async ({
        page,
        loginAs,
        credentials,
    }) => {
        await loginAs(credentials.admin);

        await page.goto('/');
        await page
            .locator(
                '[data-testid="product-card-pro"] [data-testid="get-started-button"]',
            )
            .click();

        const checkoutPage = new CheckoutPage(page);
        await checkoutPage.waitForCheckoutPage();

        // Intercept the Stripe redirect so the navigation completes in the test environment
        await page.route('https://checkout.stripe.com/**', (route) => {
            route.fulfill({
                status: 200,
                body: '<html><body>Stripe</body></html>',
            });
        });

        await checkoutPage.mockSubmitWithStripeRedirect();

        const navigationPromise = page.waitForURL(/checkout\.stripe\.com/);
        await checkoutPage.submit();
        await navigationPromise;

        await expect(page).toHaveURL(/checkout\.stripe\.com/);
    });
});
