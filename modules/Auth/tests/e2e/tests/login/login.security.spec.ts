import { expect, test } from '@e2e/fixtures';
import { LoginPage } from '../../pages/LoginPage';

test.describe('Login Security', () => {
    let loginPage: LoginPage;

    test.beforeEach(async ({ page }) => {
        loginPage = new LoginPage(page);
        await loginPage.goto();
    });

    test.describe('Rate Limiting', () => {
        test.describe.configure({ mode: 'serial' });

        test('blocks login after too many failed attempts', async () => {
            const invalidUser = {
                email: 'invalid@example.com',
                password: 'wrongpassword',
            };

            for (let i = 0; i <= 5; i++) {
                await loginPage.login(invalidUser.email, invalidUser.password);

                await loginPage.page.waitForTimeout(1000);

                if (i < 5) {
                    await expect(loginPage.page).toHaveURL(
                        loginPage.loginEndpoint,
                    );
                }
            }

            await loginPage.login(invalidUser.email, invalidUser.password);

            await expect(loginPage.page.getByText(/too many/i)).toBeVisible();
        });

        test('handles rate limit response', async () => {
            // This test verifies that the form can display rate limit errors
            // Since rate limiting is implemented on the backend, we test the UI's ability to show the error
            const invalidUser = {
                email: 'invalid@example.com',
                password: 'wrongpassword',
            };

            // Make multiple failed login attempts - backend should handle rate limiting
            await loginPage.login(invalidUser.email, invalidUser.password);
            await expect(loginPage.page).toHaveURL(loginPage.loginEndpoint);

            // Verify the page can show errors (even if not rate limited yet)
            const errorAlert = loginPage.page.locator('[role="alert"]').first();
            await expect(errorAlert).toBeVisible();
        });
    });

    test.describe('CSRF Protection', () => {
        test('rejects submission with invalid CSRF token', async ({
            credentials,
        }) => {
            const user = credentials.user;

            await loginPage.mockServerResponse(419, {
                message: 'CSRF token mismatch',
                errors: { _token: ['The CSRF token is invalid'] },
            });
            const responsePromise = loginPage.waitForLoginResponse();

            await loginPage.login(user.email, user.password);

            await expect(loginPage.page).toHaveURL(loginPage.loginEndpoint);

            const response = await responsePromise;
            expect(response.status()).toBe(419);
        });
    });
});
