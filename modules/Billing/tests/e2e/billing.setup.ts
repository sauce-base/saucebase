import { test as setup } from '@saucebase/laravel-playwright';

setup('seed billing subscriber fixtures', async ({ laravel }) => {
    await laravel.callFunction(
        'Modules\\Billing\\Tests\\Support\\BillingTestHelper::createSubscriberFixtures',
    );
});
