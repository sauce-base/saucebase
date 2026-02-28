import { faker } from '@faker-js/faker';
import { expect, test } from '@playwright/test';
import { ForgotPasswordPage } from '../../pages/ForgotPasswordPage';

test.describe.parallel('Forgot Password Basics', () => {
    let forgotPasswordPage: ForgotPasswordPage;

    test.beforeEach(async ({ page }) => {
        forgotPasswordPage = new ForgotPasswordPage(page);
        await forgotPasswordPage.goto();
        await forgotPasswordPage.expectToBeVisible();
    });

    async function expectSuccessfulPasswordReset() {
        await expect(forgotPasswordPage.page.getByRole('alert')).toHaveRole(
            'alert',
        );
    }

    test('resets password with valid email and redirects to login', async () => {
        const email = faker.internet.exampleEmail();

        await forgotPasswordPage.resetPassword(email);

        await expectSuccessfulPasswordReset();
    });

    test('submits form on Enter key press', async () => {
        const email = faker.internet.exampleEmail();
        await forgotPasswordPage.emailInput.fill(email);

        await forgotPasswordPage.emailInput.press('Enter');

        await expectSuccessfulPasswordReset();
    });
});
