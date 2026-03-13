import { test, expect } from '@e2e/fixtures';
import { RegisterPage } from '../../pages/RegisterPage';
import { faker } from '@faker-js/faker';

test.describe.parallel('Register Basics', () => {
    let registerPage: RegisterPage;

    test.beforeEach(async ({ page }) => {
        registerPage = new RegisterPage(page);
        await registerPage.goto();
        await registerPage.expectToBeVisible();
    });

    async function expectSuccessfulRegistration() {
        await expect(registerPage.page).toHaveURL(registerPage.redirectEndpoint);
    }

    function newUser() {
        return {
            name: faker.person.fullName(),
            email: faker.internet.exampleEmail(),
            password: faker.internet.password(),
        };
    }


    test('registers with valid details and redirects to dashboard', async () => {
        const user = newUser(); // Using the newUser function to generate user details

        await registerPage.register(user.name, user.email, user.password);

        await expectSuccessfulRegistration();
    });

    test('toggles password visibility', async () => {

        await registerPage.passwordInput.fill('password123');

        await registerPage.expectPasswordHidden();

        await registerPage.togglePasswordVisibility();
        await registerPage.expectPasswordVisible();

        await registerPage.togglePasswordVisibility();
        await registerPage.expectPasswordHidden();
    });

    test('submits form on Enter key press', async () => {
        const user = newUser();
        await registerPage.nameInput.fill(user.name);
        await registerPage.emailInput.fill(user.email);
        await registerPage.passwordInput.fill(user.password);

        await registerPage.passwordInput.press('Enter');

        await expectSuccessfulRegistration();
    });
});
